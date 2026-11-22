<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Lead;
use App\Models\Province;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminLeadsAndSidebarTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(bool $isSuper = true): Admin
    {
        return Admin::create([
            'employee_code' => 'ADM-TEST-' . uniqid(),
            'full_name' => 'Nguyễn Quản Trị Hệ Thống',
            'email' => 'admin.' . uniqid() . '@officeleasing.vn',
            'phone' => '0912345678',
            'password' => 'secret123',
            'is_super_admin' => $isSuper,
            'status' => 'ACTIVE',
        ]);
    }

    protected function createBuilding(): Building
    {
        Province::firstOrCreate(
            ['code' => 79],
            [
                'name' => 'TP Hồ Chí Minh',
                'slug' => 'tp-ho-chi-minh',
                'code_name' => 'thanh_pho_ho_chi_minh',
            ]
        );

        $code = 'BLD-' . strtoupper(uniqid());
        return Building::create([
            'building_code' => $code,
            'name' => 'Tòa Nhà Thử Nghiệm ' . $code,
            'slug' => strtolower($code),
            'address' => '123 Đường Mẫu',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 20,
            'total_leasable_area_sqm' => 5000.0,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 20000,
            'default_service_fee_per_sqm' => 150000,
            'min_rent_price_per_sqm' => 850000,
            'grade' => 'A',
            'is_published' => true,
        ]);
    }

    protected function createSale(string $code = 'SL-01', string $name = 'Sales Test'): Sale
    {
        return Sale::create([
            'employee_code' => $code . '-' . uniqid(),
            'full_name' => $name,
            'email' => strtolower($code) . '.' . uniqid() . '@officeleasing.vn',
            'phone' => '09' . str_pad((string) mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => 'secret123',
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_admin_sidebar_has_crm_leads_and_no_subsystem_access(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'web')->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Quản lý Leads CRM');
        $response->assertDontSee('TRUY CẬP PHÂN HỆ');
        $response->assertDontSee('Cổng Kinh Doanh (Sales)');
        $response->assertDontSee('Cổng Ban Quản Lý (PM)');
        $response->assertDontSee('Cổng Kế Toán Tòa Nhà');
    }

    public function test_admin_can_access_leads_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'web')->get(route('admin.leads'));

        $response->assertStatus(200);
        $response->assertSee('Quản lý Leads CRM');
        $response->assertSee('Chờ phân bổ');
    }

    public function test_admin_can_filter_and_assign_unassigned_lead(): void
    {
        $admin = $this->createAdmin();
        Sanctum::actingAs($admin, ['*']);

        $building = $this->createBuilding();
        $sale = $this->createSale('SL-TEST-01', 'Trần Sales 1');

        $unassignedLead = Lead::create([
            'contact_full_name' => 'Nguyễn Khách Hàng',
            'phone' => '0988112233',
            'email' => 'khach.' . uniqid() . '@doanhnghiep.vn',
            'company_name' => 'Công Ty Thử Nghiệm',
            'building_id' => $building->id,
            'lead_source' => 'WEBSITE',
            'status' => LeadStatus::PENDING_ASSIGN,
            'assigned_sales_id' => null,
            'sla_deadline_at' => now()->addHours(24),
        ]);

        // 1. Filter unassigned leads
        $filterResponse = $this->getJson('/api/v1/crm/leads?pipeline_status=PENDING_ASSIGN');
        $filterResponse->assertStatus(200);
        $this->assertNotEmpty($filterResponse->json('data'));

        // 2. Admin assigns lead using sales_id
        $assignResponse = $this->postJson("/api/v1/crm/leads/{$unassignedLead->id}/assign", [
            'sales_id' => $sale->id,
            'assignment_note' => 'Admin chỉ định Sales thủ công',
        ]);

        $assignResponse->assertStatus(200);
        $unassignedLead->refresh();
        $this->assertEquals($sale->id, $unassignedLead->assigned_sales_id);
        $this->assertEquals(LeadStatus::NEW, $unassignedLead->status);

        // 3. Admin reassigns lead using assigned_sales_id payload
        $anotherSale = $this->createSale('SL-TEST-02', 'Lê Sales 2');
        $reassignResponse = $this->postJson("/api/v1/crm/leads/{$unassignedLead->id}/assign", [
            'assigned_sales_id' => $anotherSale->id,
            'assignment_note' => 'Điều phối sang Sales khác',
        ]);
        $reassignResponse->assertStatus(200);
        $unassignedLead->refresh();
        $this->assertEquals($anotherSale->id, $unassignedLead->assigned_sales_id);
    }
}
