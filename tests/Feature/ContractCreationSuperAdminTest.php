<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractCreationSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected string $superAdminToken;

    protected Sale $saleUser;
    protected Building $buildingA;
    protected Building $buildingB;
    protected Tenant $tenantWithSale;
    protected Unit $unitInBuildingB;

    protected function setUp(): void
    {
        parent::setUp();

        $province = Province::create([
            'code' => 79,
            'name' => 'Thành phố Hồ Chí Minh',
            'slug' => 'thanh-pho-ho-chi-minh',
            'division_type' => 'thành phố trung ương',
            'codename' => 'ho_chi_minh',
            'phone_code' => 28,
        ]);

        // 1. Tạo Super Admin
        $this->superAdmin = Admin::create([
            'full_name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('Password123!'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);
        $this->superAdminToken = $this->superAdmin->createToken('super_admin_test_token')->plainTextToken;

        // 2. Tạo Sales chỉ được phân công quản lý Tòa nhà A
        $this->saleUser = Sale::create([
            'employee_code' => 'SALE-001',
            'full_name' => 'Sales Person A',
            'email' => 'sales_a@example.com',
            'phone' => '0901234567',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);

        $this->buildingA = Building::create([
            'building_code' => 'BLD-A',
            'name' => 'Tòa nhà A',
            'slug' => 'toa-nha-a',
            'province_code' => $province->code,
            'address' => '123 Đường A',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'total_floors' => 10,
            'total_leasable_area_sqm' => 5000.0,
        ]);
        $this->saleUser->buildings()->attach($this->buildingA->id);

        // 3. Tạo Tòa nhà B (Sales A KHÔNG được phân công)
        $this->buildingB = Building::create([
            'building_code' => 'BLD-B',
            'name' => 'Tòa nhà B (Sales A không phụ trách)',
            'slug' => 'toa-nha-b',
            'province_code' => $province->code,
            'address' => '456 Đường B',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'total_floors' => 15,
            'total_leasable_area_sqm' => 8000.0,
        ]);

        $this->unitInBuildingB = Unit::create([
            'building_id' => $this->buildingB->id,
            'unit_code' => 'L05-01',
            'name' => 'Văn phòng L05-01',
            'floor_number' => 5,
            'area_sqm' => 150.0,
            'base_rental_price' => 500000,
            'status' => 'AVAILABLE',
            'handover_condition' => 'BASIC',
        ]);

        // 4. Tạo Khách thuê được tạo bởi Sales A
        $this->tenantWithSale = Tenant::create([
            'tenant_code' => 'TNT-2026-0001',
            'company_name' => 'Công ty TNHH Công Nghệ Beta',
            'tax_code' => '0318888888',
            'business_license_private_path' => 'licenses/beta.pdf',
            'representative_name' => 'Trần Văn Beta',
            'representative_position' => 'Giám Đốc Điều Hành',
            'headquarter_address' => '100 Nguyễn Huệ, P. Bến Nghé, Quận 1',
            'login_email' => 'login@beta.vn',
            'billing_email' => 'ketoan@beta.vn',
            'contact_phone' => '0908888888',
            'password' => 'secret123',
            'created_by_sales_id' => $this->saleUser->id,
        ]);
    }

    /**
     * Test Super Admin có thể tạo hợp đồng nháp cho bất kỳ tòa nhà nào,
     * ngay cả khi khách thuê thuộc quản lý của nhân viên Sales không phụ trách tòa nhà đó.
     */
    public function test_super_admin_can_create_draft_contract_for_any_building_and_tenant(): void
    {
        $payload = [
            'building_id' => $this->buildingB->id,
            'tenant_id' => $this->tenantWithSale->id,
            'start_date' => '2026-12-01',
            'end_date' => '2028-11-30',
            'deposit_amount' => 150000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'units' => [
                [
                    'unit_id' => $this->unitInBuildingB->id,
                    'leased_area_sqm' => 150.0,
                    'agreed_rental_price_per_sqm' => 500000,
                    'agreed_service_fee_per_sqm' => 50000,
                ]
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/contracts', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.building_id', $this->buildingB->id)
            ->assertJsonPath('data.tenant_id', $this->tenantWithSale->id)
            ->assertJsonPath('data.status', ContractStatus::DRAFT->value);
    }

    /**
     * Test Super Admin có thể đặt cọc giữ chỗ cho bất kỳ tòa nhà nào.
     */
    public function test_super_admin_can_create_booking_contract_for_any_building_and_tenant(): void
    {
        $payload = [
            'building_id' => $this->buildingB->id,
            'tenant_id' => $this->tenantWithSale->id,
            'start_date' => '2026-12-01',
            'end_date' => '2028-11-30',
            'booking_deposit_amount' => 50000000,
            'reservation_expires_at' => now()->addDays(7)->toDateTimeString(),
            'units' => [
                [
                    'unit_id' => $this->unitInBuildingB->id,
                    'leased_area_sqm' => 150.0,
                    'agreed_rental_price_per_sqm' => 500000,
                    'agreed_service_fee_per_sqm' => 50000,
                ]
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/contracts/reserve', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', ContractStatus::RESERVED->value);
    }

    /**
     * Test Sales cố tình lập hợp đồng cho tòa nhà mình không phụ trách thì vẫn bị chặn 422/403.
     */
    public function test_sales_user_cannot_create_contract_for_unassigned_building(): void
    {
        $salesToken = $this->saleUser->createToken('sale_token')->plainTextToken;

        $payload = [
            'building_id' => $this->buildingB->id,
            'tenant_id' => $this->tenantWithSale->id,
            'start_date' => '2026-12-01',
            'end_date' => '2028-11-30',
            'deposit_amount' => 150000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'units' => [
                [
                    'unit_id' => $this->unitInBuildingB->id,
                    'leased_area_sqm' => 150.0,
                    'agreed_rental_price_per_sqm' => 500000,
                    'agreed_service_fee_per_sqm' => 50000,
                ]
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$salesToken}")
            ->postJson('/api/v1/admin/contracts', $payload);

        // Bị chặn bởi StoreContractRequest (422) hoặc ContractService (403)
        $this->assertTrue(in_array($response->status(), [403, 422], true));
    }
}
