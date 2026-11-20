<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature Test Suite: Xác thực, Phân quyền RBAC & Phân lập Dữ liệu (Data Isolation).
 * Kiểm thử toàn diện 6 vai trò: Admin, PM, Sales, Accountant, Tenant, Guest.
 */
class AuthAndRbacIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProvinceSeeder::class);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-ISO-01',
            'full_name' => 'Super Admin Isolation',
            'email' => 'superadmin.iso@office.vn',
            'phone' => '0911000999',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
            'is_super_admin' => true,
        ]);
        $this->adminToken = $this->admin->createToken('admin_token', ['admin'])->plainTextToken;
    }

    /**
     * 1. Guest: Không có token bị chặn 401 Unauthorized khi vào API private.
     */
    public function test_guest_is_blocked_with_401_from_private_api_routes(): void
    {
        $this->getJson('/api/v1/admin/buildings')->assertStatus(401);
        $this->getJson('/api/v1/admin/units')->assertStatus(401);
        $this->getJson('/api/v1/crm/leads')->assertStatus(401);
        $this->getJson('/api/v1/admin/contracts')->assertStatus(401);
        $this->getJson('/api/v1/tenant/contracts')->assertStatus(401);
    }

    /**
     * 2. Guest: Xem được danh sách tòa nhà công khai trên Portal.
     */
    public function test_guest_can_view_published_buildings_on_public_portal(): void
    {
        $this->createBuilding('BLD-PUB-01', 'Tòa Công Khai', true);
        $this->createBuilding('BLD-DRF-02', 'Tòa Nháp Ẩn', false);

        $response = $this->getJson('/api/v1/portal/buildings');
        $response->assertStatus(200);

        $data = $response->json('data');
        $names = collect($data)->pluck('name')->toArray();

        $this->assertContains('Tòa Công Khai', $names);
        $this->assertNotContains('Tòa Nháp Ẩn', $names);
    }

    /**
     * 3. PM Data Isolation: PM 1 chỉ xem tòa nhà và sàn của tòa nhà mình được phân công.
     */
    public function test_property_manager_data_isolation_for_buildings_and_units(): void
    {
        $buildingA = $this->createBuilding('BLD-LM81', 'Tòa Landmark 81', true);
        $buildingB = $this->createBuilding('BLD-BTX', 'Tòa Bitexco', true);

        $pm1 = PropertyManager::create([
            'employee_code' => 'PM-ISO-01',
            'full_name' => 'PM Landmark',
            'email' => 'pm1.iso@office.vn',
            'phone' => '0912000001',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $pm2 = PropertyManager::create([
            'employee_code' => 'PM-ISO-02',
            'full_name' => 'PM Bitexco',
            'email' => 'pm2.iso@office.vn',
            'phone' => '0912000002',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $pm1->buildings()->attach($buildingA->id);
        $pm2->buildings()->attach($buildingB->id);

        $unitA = $this->createUnit($buildingA, 'LM-101', 'Sàn Landmark 101');
        $unitB = $this->createUnit($buildingB, 'BTX-201', 'Sàn Bitexco 201');

        $tokenPm1 = $pm1->createToken('pm1_token', ['property_manager'])->plainTextToken;

        // PM 1 lấy danh sách tòa nhà -> chỉ thấy Tòa Landmark 81
        $resBuildings = $this->withHeader('Authorization', "Bearer {$tokenPm1}")
            ->getJson('/api/v1/admin/buildings');
        $resBuildings->assertStatus(200);
        $buildingIds = collect($resBuildings->json('data'))->pluck('id')->toArray();
        $this->assertContains($buildingA->id, $buildingIds);
        $this->assertNotContains($buildingB->id, $buildingIds);

        // PM 1 lấy danh sách sàn -> chỉ thấy sàn của Tòa Landmark 81
        $resUnits = $this->withHeader('Authorization', "Bearer {$tokenPm1}")
            ->getJson('/api/v1/admin/units');
        $resUnits->assertStatus(200);
        $unitCodes = collect($resUnits->json('data'))->pluck('unit_code')->toArray();
        $this->assertContains('LM-101', $unitCodes);
        $this->assertNotContains('BTX-201', $unitCodes);
    }

    /**
     * 4. PM RBAC: PM không được phép tạo mới tòa nhà (chỉ Admin).
     */
    public function test_property_manager_cannot_create_building(): void
    {
        $pm = PropertyManager::create([
            'employee_code' => 'PM-ISO-03',
            'full_name' => 'PM Test RBAC',
            'email' => 'pm3.iso@office.vn',
            'phone' => '0912000003',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $tokenPm = $pm->createToken('pm_token', ['property_manager'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$tokenPm}")
            ->postJson('/api/v1/admin/buildings', [
                'name' => 'Tòa Nhà Bất Hợp Pháp',
                'building_code' => 'TNBHP',
                'address' => '123 Test',
                'ward' => 'Phường Bến Nghé',
                'district' => 'Quận 1',
                'province' => 'TP.HCM',
            ]);

        $response->assertStatus(403);
    }

    /**
     * 5. Sales Data Isolation: Sales A chỉ xem Lead của chính mình, không xem được Lead của Sales B.
     */
    public function test_sales_lead_data_isolation(): void
    {
        $saleA = Sale::create([
            'employee_code' => 'SALE-ISO-01',
            'full_name' => 'Sales Alpha',
            'email' => 'sale.alpha@office.vn',
            'phone' => '0901000001',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $saleB = Sale::create([
            'employee_code' => 'SALE-ISO-02',
            'full_name' => 'Sales Beta',
            'email' => 'sale.beta@office.vn',
            'phone' => '0901000002',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $leadA = Lead::create([
            'contact_full_name' => 'Khách của Alpha',
            'phone' => '0912345671',
            'email' => 'khach.a@test.vn',
            'assigned_sales_id' => $saleA->id,
            'status' => 'NEW',
        ]);
        $leadB = Lead::create([
            'contact_full_name' => 'Khách của Beta',
            'phone' => '0912345672',
            'email' => 'khach.b@test.vn',
            'assigned_sales_id' => $saleB->id,
            'status' => 'NEW',
        ]);

        $tokenSaleA = $saleA->createToken('sale_a_token', ['sales'])->plainTextToken;

        // Sales A lấy danh sách Leads -> chỉ thấy leadA
        $response = $this->withHeader('Authorization', "Bearer {$tokenSaleA}")
            ->getJson('/api/v1/crm/leads');
        $response->assertStatus(200);

        $leadNames = collect($response->json('data'))->pluck('contact_full_name')->toArray();
        $this->assertContains('Khách của Alpha', $leadNames);
        $this->assertNotContains('Khách của Beta', $leadNames);

        // Sales A cố xem chi tiết Lead của Sales B -> 403 Forbidden
        $resShow = $this->withHeader('Authorization', "Bearer {$tokenSaleA}")
            ->getJson("/api/v1/crm/leads/{$leadB->id}");
        $resShow->assertStatus(403);

        // Sales A cố ghi nhật ký chăm sóc vào Lead của Sales B -> 403 Forbidden
        $resLog = $this->withHeader('Authorization', "Bearer {$tokenSaleA}")
            ->postJson("/api/v1/crm/leads/{$leadB->id}/activities", [
                'activity_type' => 'CALL',
                'title' => 'Gọi điện lén lút',
            ]);
        $resLog->assertStatus(403);
    }

    /**
     * 6. Sales RBAC: Sales không được quyền tự ý điều phối / phân công Lead (chỉ Admin).
     */
    public function test_sales_cannot_reassign_lead_route_forbidden(): void
    {
        $saleA = Sale::create([
            'employee_code' => 'SALE-ISO-03',
            'full_name' => 'Sales Lead Owner',
            'email' => 'sale3.iso@office.vn',
            'phone' => '0901000003',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $saleB = Sale::create([
            'employee_code' => 'SALE-ISO-04',
            'full_name' => 'Sales Target',
            'email' => 'sale4.iso@office.vn',
            'phone' => '0901000004',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $lead = Lead::create([
            'contact_full_name' => 'Khách Cần Chuyển',
            'phone' => '0912345673',
            'email' => 'khach.c@test.vn',
            'assigned_sales_id' => $saleA->id,
            'status' => 'NEW',
        ]);

        $tokenSaleA = $saleA->createToken('sale_token', ['sales'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$tokenSaleA}")
            ->postJson("/api/v1/crm/leads/{$lead->id}/assign", [
                'sales_id' => $saleB->id,
                'assignment_note' => 'Tự ý chuyển lead',
            ]);

        $response->assertStatus(403);
    }

    /**
     * 7. Sales Contract Workflow: Sales xem được hợp đồng do mình tạo nhưng không thể ký duyệt hoặc thanh lý.
     */
    public function test_sales_contract_workflow_view_ok_sign_terminate_blocked(): void
    {
        $building = $this->createBuilding('BLD-SALE-M', 'Tòa Sales Quản Lý', true);
        $sale = Sale::create([
            'employee_code' => 'SALE-ISO-05',
            'full_name' => 'Sales Deal Closer',
            'email' => 'sale5.iso@office.vn',
            'phone' => '0901000005',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $tenant = $this->createTenant('TNT-ISO-01', 'Công ty Alpha Tech');

        $contract = $this->createContract($building, $tenant, 'HD-SALE-001', $sale->id);

        $tokenSale = $sale->createToken('sale_token', ['sales'])->plainTextToken;

        // Sales xem danh sách và chi tiết hợp đồng do mình tạo -> 200 OK
        $resList = $this->withHeader('Authorization', "Bearer {$tokenSale}")
            ->getJson('/api/v1/admin/contracts');
        $resList->assertStatus(200);

        $resDetail = $this->withHeader('Authorization', "Bearer {$tokenSale}")
            ->getJson("/api/v1/admin/contracts/{$contract->id}");
        $resDetail->assertStatus(200);

        // Sales cố kích hoạt ký duyệt hợp đồng -> 403 Forbidden
        $resSign = $this->withHeader('Authorization', "Bearer {$tokenSale}")
            ->postJson("/api/v1/admin/contracts/{$contract->id}/sign");
        $resSign->assertStatus(403);

        // Sales cố thanh lý hợp đồng -> 403 Forbidden
        $resTerm = $this->withHeader('Authorization', "Bearer {$tokenSale}")
            ->postJson("/api/v1/admin/contracts/{$contract->id}/terminate", [
                'refund_deposit_amount' => 1000,
                'penalty_deposit_amount' => 0,
                'termination_note' => 'Sales tự hủy',
            ]);
        $resTerm->assertStatus(403);
    }

    /**
     * 8. Accountant Data Isolation: Kế toán chỉ xem hợp đồng của tòa nhà mình được phân công (account_buildings).
     */
    public function test_accountant_building_isolation_for_contracts(): void
    {
        $buildingA = $this->createBuilding('BLD-PK1', 'Tòa Park 1', true);
        $buildingB = $this->createBuilding('BLD-PK2', 'Tòa Park 2', true);

        $accountant = Accountant::create([
            'employee_code' => 'ACC-ISO-01',
            'full_name' => 'Kế toán Park 1',
            'email' => 'acc1.iso@office.vn',
            'phone' => '0981000001',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $accountant->buildings()->attach($buildingA->id);

        $tenantA = $this->createTenant('TNT-PK1', 'Công ty Park 1');
        $tenantB = $this->createTenant('TNT-PK2', 'Công ty Park 2');

        $contractA = $this->createContract($buildingA, $tenantA, 'HD-PARK1-001');
        $contractB = $this->createContract($buildingB, $tenantB, 'HD-PARK2-002');

        $tokenAccountant = $accountant->createToken('kt_token', ['accountant'])->plainTextToken;

        // Kế toán lấy danh sách hợp đồng -> chỉ thấy contractA
        $resList = $this->withHeader('Authorization', "Bearer {$tokenAccountant}")
            ->getJson('/api/v1/admin/contracts');
        $resList->assertStatus(200);

        $contractNumbers = collect($resList->json('data'))->pluck('contract_number')->toArray();
        $this->assertContains('HD-PARK1-001', $contractNumbers);
        $this->assertNotContains('HD-PARK2-002', $contractNumbers);

        // Kế toán cố xem chi tiết hợp đồng thuộc Tòa Park 2 (không phụ trách) -> 403 Forbidden
        $resDetail = $this->withHeader('Authorization', "Bearer {$tokenAccountant}")
            ->getJson("/api/v1/admin/contracts/{$contractB->id}");
        $resDetail->assertStatus(403);

        // Kế toán cố ký duyệt hợp đồng -> 403 Forbidden
        $resSign = $this->withHeader('Authorization', "Bearer {$tokenAccountant}")
            ->postJson("/api/v1/admin/contracts/{$contractA->id}/sign");
        $resSign->assertStatus(403);
    }

    /**
     * 9. Tenant Anti-IDOR: Khách thuê không thể xem hợp đồng hoặc gọi API của Admin.
     */
    public function test_tenant_anti_idor_and_isolation(): void
    {
        $tenantAlpha = $this->createTenant('TNT-ALP', 'Doanh Nghiệp Alpha');
        $tenantBeta = $this->createTenant('TNT-BET', 'Doanh Nghiệp Beta');

        $building = $this->createBuilding('BLD-TEN-M', 'Tòa Cho Khách Thuê', true);

        $contractAlpha = $this->createContract($building, $tenantAlpha, 'HD-ALPHA-01');
        $contractBeta = $this->createContract($building, $tenantBeta, 'HD-BETA-02');

        $tokenAlpha = $tenantAlpha->createToken('alpha_token', ['tenant'])->plainTextToken;

        // Tenant Alpha xem danh sách hợp đồng của mình -> chỉ thấy contractAlpha
        $resList = $this->withHeader('Authorization', "Bearer {$tokenAlpha}")
            ->getJson('/api/v1/tenant/contracts');
        $resList->assertStatus(200);
        $contractNumbers = collect($resList->json('data'))->pluck('contract_number')->toArray();
        $this->assertContains('HD-ALPHA-01', $contractNumbers);
        $this->assertNotContains('HD-BETA-02', $contractNumbers);

        // Tenant Alpha cố xem hợp đồng của Tenant Beta qua ID (IDOR) -> 403 Forbidden
        $resIdor = $this->withHeader('Authorization', "Bearer {$tokenAlpha}")
            ->getJson("/api/v1/tenant/contracts/{$contractBeta->id}");
        $resIdor->assertStatus(403);

        // Tenant Alpha cố gọi API Admin -> 403 Forbidden
        $resAdmin = $this->withHeader('Authorization', "Bearer {$tokenAlpha}")
            ->getJson('/api/v1/admin/contracts');
        $resAdmin->assertStatus(403);
    }

    /**
     * 10. Admin: Toàn quyền (Global Scope Bypass) xem mọi tòa nhà trên hệ thống.
     */
    public function test_admin_has_global_scope_access(): void
    {
        $this->createBuilding('BLD-GLB-1', 'Tòa Toàn Quyền A', true);
        $this->createBuilding('BLD-GLB-2', 'Tòa Toàn Quyền B', true);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/buildings');

        $response->assertStatus(200);
        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('Tòa Toàn Quyền A', $names);
        $this->assertContains('Tòa Toàn Quyền B', $names);
    }

    // --- Helper Methods ---

    protected function createBuilding(string $code, string $name, bool $published): Building
    {
        return Building::create([
            'building_code' => $code,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . rand(100, 999),
            'total_floors' => 10,
            'total_leasable_area_sqm' => 5000,
            'address' => '123 Đường Test',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'TP.HCM',
            'grade' => 'B',
            'is_published' => $published,
        ]);
    }

    protected function createUnit(Building $building, string $code, string $name): Unit
    {
        return Unit::create([
            'building_id' => $building->id,
            'unit_code' => $code,
            'name' => $name,
            'floor_number' => 2,
            'area_sqm' => 100.0,
            'base_rental_price' => 500000,
            'handover_condition' => 'BASIC',
            'status' => UnitStatus::AVAILABLE,
        ]);
    }

    protected function createTenant(string $code, string $companyName): Tenant
    {
        return Tenant::create([
            'tenant_code' => $code,
            'company_name' => $companyName,
            'tax_code' => '03' . rand(10000000, 99999999),
            'business_license_private_path' => 'licenses/' . Str::slug($code) . '.pdf',
            'representative_name' => 'Đại Diện ' . $companyName,
            'representative_position' => 'Giám Đốc',
            'headquarter_address' => 'Địa chỉ ' . $companyName,
            'login_email' => Str::slug($code) . '@tenant.vn',
            'billing_email' => 'kt.' . Str::slug($code) . '@tenant.vn',
            'contact_phone' => '09' . rand(10000000, 99999999),
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
    }

    protected function createContract(Building $building, Tenant $tenant, string $contractNumber, ?string $salesId = null): Contract
    {
        return Contract::create([
            'contract_number' => $contractNumber,
            'building_id' => $building->id,
            'tenant_id' => $tenant->id,
            'created_by_sales_id' => $salesId,
            'status' => ContractStatus::DRAFT,
            'signed_date' => now()->toDateString(),
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(365)->toDateString(),
            'total_leased_area_sqm' => 100,
            'monthly_rent_amount' => 50000000,
            'monthly_service_fee' => 5000000,
            'deposit_amount' => 150000000,
            'billing_cycle_months' => 3,
            'payment_term_days' => 5,
        ]);
    }
}
