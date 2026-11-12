<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Events\LeadAssignedToSalesEvent;
use App\Events\LeadPendingAssignmentEvent;
use App\Models\Building;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class Module2LeadApiTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Building $building;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'code' => 79,
            'name' => 'TP Hồ Chí Minh',
            'slug' => 'tp-ho-chi-minh',
            'code_name' => 'thanh_pho_ho_chi_minh',
        ]);

        $this->building = Building::create([
            'building_code' => 'B-TEST-001',
            'name' => 'Bitexco Financial Tower',
            'slug' => 'bitexco-financial-tower',
            'address' => '2 Hải Triều',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 68,
            'total_leasable_area_sqm' => 37000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 20000,
            'default_service_fee_per_sqm' => 150000,
            'min_rent_price_per_sqm' => 850000,
            'grade' => 'A',
            'is_published' => true,
        ]);

        $this->unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.1201',
            'name' => 'Văn phòng P.1201',
            'floor_number' => 12,
            'area_sqm' => 250,
            'base_rental_price' => 950000,
            'status' => 'AVAILABLE',
        ]);
    }

    protected function createSales(string $name, string $code, int $maxLeads = 10): Sale
    {
        $sale = Sale::create([
            'employee_code' => $code,
            'full_name' => $name,
            'email' => strtolower($code) . '@office.vn',
            'phone' => '090000000' . rand(1, 9),
            'password' => bcrypt('password123'),
            'commission_rate' => 1.5,
            'max_concurrent_leads' => $maxLeads,
            'status' => 'ACTIVE',
        ]);

        $sale->buildings()->attach($this->building->id, [
            'is_active' => true,
        ]);

        return $sale;
    }

    /**
     * Test phân bổ Lead cho Sales có ít Lead active nhất.
     */
    public function test_public_portal_submits_lead_and_allocates_to_least_active_sales(): void
    {
        Event::fake([LeadAssignedToSalesEvent::class]);

        $saleA = $this->createSales('Nguyễn Văn A', 'SALE-A', 10);
        $saleB = $this->createSales('Trần Thị B', 'SALE-B', 10);

        // Sale A đang có 2 active leads
        Lead::create([
            'contact_full_name' => 'Khách Cũ 1',
            'phone' => '0911111111',
            'email' => 'old1@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $saleA->id,
            'status' => LeadStatus::NEW,
        ]);
        Lead::create([
            'contact_full_name' => 'Khách Cũ 2',
            'phone' => '0911111112',
            'email' => 'old2@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $saleA->id,
            'status' => LeadStatus::NEGOTIATING,
        ]);

        // Sale B có 0 active leads -> Phải được chọn!
        $response = $this->postJson('/api/v1/portal/leads', [
            'contact_full_name' => 'Phạm Minh Mới',
            'company_name' => 'Công Ty Công Nghệ Alpha',
            'phone' => '0988776655',
            'email' => 'alpha@tech.vn',
            'building_id' => $this->building->id,
            'unit_id' => $this->unit->id,
            'desired_area_sqm' => 250,
            'customer_note' => 'Cần xem mặt bằng vào sáng thứ Hai',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.assigned_sales_id', $saleB->id)
            ->assertJsonPath('data.status', 'NEW');

        Event::assertDispatched(LeadAssignedToSalesEvent::class);
    }

    /**
     * Test Tie-Breaker: Khi số Lead active bằng nhau, ưu tiên người có last_assigned_at sớm nhất.
     */
    public function test_tie_breaker_allocates_to_sales_with_earliest_last_assigned_at(): void
    {
        Event::fake([LeadAssignedToSalesEvent::class]);

        $saleA = $this->createSales('Sales A', 'SALE-A1', 10);
        $saleB = $this->createSales('Sales B', 'SALE-B1', 10);

        // Cả 2 cùng có 1 active lead, nhưng Sale A nhận hôm nay, Sale B nhận 5 ngày trước
        Lead::create([
            'contact_full_name' => 'Khách Sale A',
            'phone' => '0922222221',
            'email' => 'a@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $saleA->id,
            'assigned_at' => now(),
            'status' => LeadStatus::NEW,
        ]);

        Lead::create([
            'contact_full_name' => 'Khách Sale B',
            'phone' => '0922222222',
            'email' => 'b@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $saleB->id,
            'assigned_at' => now()->subDays(5),
            'status' => LeadStatus::NEW,
        ]);

        // Gửi Lead mới -> Sale B nhận (vì thời điểm nhận lead cũ xa hơn)
        $response = $this->postJson('/api/v1/portal/leads', [
            'contact_full_name' => 'Khách Hàng Tie Breaker',
            'phone' => '0933333333',
            'email' => 'tie@break.vn',
            'building_id' => $this->building->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.assigned_sales_id', $saleB->id);
    }

    /**
     * Test Fallback sang PENDING_ASSIGN khi toàn bộ Sales chạm trần tải.
     */
    public function test_fallback_to_pending_assign_when_all_sales_exceed_concurrency_cap(): void
    {
        Event::fake([LeadPendingAssignmentEvent::class]);

        $saleFull = $this->createSales('Sales Bận', 'SALE-FULL', 1);

        // Đã đạt trần tải 1 lead
        Lead::create([
            'contact_full_name' => 'Khách 1',
            'phone' => '0944444441',
            'email' => 'c1@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $saleFull->id,
            'status' => LeadStatus::NEW,
        ]);

        $response = $this->postJson('/api/v1/portal/leads', [
            'contact_full_name' => 'Khách Chờ Phân Bổ',
            'phone' => '0955555555',
            'email' => 'pending@mail.com',
            'building_id' => $this->building->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'PENDING_ASSIGN')
            ->assertJsonPath('data.assigned_sales_id', null);

        Event::assertDispatched(LeadPendingAssignmentEvent::class);
    }

    /**
     * Test chống trùng lặp 30 ngày: không tạo Lead mới, append nhật ký vào Lead cũ.
     */
    public function test_anti_duplicate_30_days_appends_activity(): void
    {
        $sale = $this->createSales('Sales Chăm Sóc', 'SALE-CARE', 10);

        // Gửi lần 1
        $res1 = $this->postJson('/api/v1/portal/leads', [
            'contact_full_name' => 'Nguyễn Trùng Lặp',
            'phone' => '0966666666',
            'email' => 'duplicate@mail.com',
            'building_id' => $this->building->id,
            'customer_note' => 'Yêu cầu lần 1',
        ]);
        $res1->assertStatus(201);
        $leadId = $res1->json('data.id');

        // Gửi lần 2 với cùng số điện thoại
        $res2 = $this->postJson('/api/v1/portal/leads', [
            'contact_full_name' => 'Nguyễn Trùng Lặp',
            'phone' => '0966666666',
            'email' => 'other_mail@mail.com',
            'building_id' => $this->building->id,
            'customer_note' => 'Yêu cầu lần 2 gửi lại',
        ]);

        $res2->assertStatus(201)
            ->assertJsonPath('data.id', $leadId);

        // Tổng số Lead vẫn chỉ là 1
        $this->assertEquals(1, Lead::where('phone', '0966666666')->count());

        // Kiểm tra đã append vào lead_activities
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $leadId,
            'activity_type' => 'NOTE',
            'content' => 'Yêu cầu lần 2 gửi lại',
        ]);
    }

    /**
     * Test chuyển đổi trạng thái Pipeline và ràng buộc lý do khi LOST.
     */
    public function test_pipeline_status_and_lost_validation(): void
    {
        $sale = $this->createSales('Sales Test', 'SALE-T1', 10);
        $lead = Lead::create([
            'contact_full_name' => 'Khách Pipeline',
            'phone' => '0977777777',
            'email' => 'pipeline@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $sale->id,
            'status' => LeadStatus::NEW,
        ]);

        // Thử chuyển sang LOST mà không có lý do -> HTTP 422
        $resFail = $this->patchJson("/api/v1/crm/leads/{$lead->id}/status", [
            'status' => 'LOST',
        ]);
        $resFail->assertStatus(422);

        // Chuyển sang LOST kèm lý do đầy đủ -> Thành công
        $resSuccess = $this->patchJson("/api/v1/crm/leads/{$lead->id}/status", [
            'status' => 'LOST',
            'lost_reason_category' => 'Giá quá cao so với ngân sách',
            'lost_reason_detail' => 'Khách chỉ có ngân sách tối đa 500k/m2.',
        ]);
        $resSuccess->assertStatus(200)
            ->assertJsonPath('data.status', 'LOST')
            ->assertJsonPath('data.lost_reason_category', 'Giá quá cao so với ngân sách');
    }

    /**
     * Test ghi nhật ký tương tác tự động cập nhật first_contacted_at.
     */
    public function test_logging_activity_triggers_first_contacted_at(): void
    {
        $sale = $this->createSales('Sales Contact', 'SALE-C1', 10);
        $lead = Lead::create([
            'contact_full_name' => 'Khách Cần Gọi',
            'phone' => '0988888888',
            'email' => 'call@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $sale->id,
            'status' => LeadStatus::NEW,
            'first_contacted_at' => null,
        ]);

        $response = $this->postJson("/api/v1/crm/leads/{$lead->id}/activities", [
            'activity_type' => 'CALL',
            'title' => 'Gọi điện chào giá và khảo sát sơ bộ',
            'content' => 'Khách đã nghe máy và hẹn 10h sáng mai gửi brochure chi tiết.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.activity_type', 'CALL');

        $lead->refresh();
        $this->assertNotNull($lead->first_contacted_at);
        $this->assertEquals(LeadStatus::NEGOTIATING, $lead->status);
    }

    /**
     * Test điều phối / Gán lại Sales và ghi audit log.
     */
    public function test_manual_assign_sales_and_system_note(): void
    {
        $saleA = $this->createSales('Sales Ban Đầu', 'SALE-INIT', 10);
        $saleB = $this->createSales('Sales Thay Thế', 'SALE-NEXT', 10);

        $lead = Lead::create([
            'contact_full_name' => 'Khách Điều Phối',
            'phone' => '0999999999',
            'email' => 'reassign@mail.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $saleA->id,
            'status' => LeadStatus::NEW,
        ]);

        $response = $this->postJson("/api/v1/crm/leads/{$lead->id}/assign", [
            'sales_id' => $saleB->id,
            'assignment_note' => 'Sales A bận công tác đột xuất.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.assigned_sales_id', $saleB->id);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'NOTE',
            'title' => 'Điều phối nhân sự phụ trách',
        ]);
    }

    /**
     * Test chuyển đổi Lead sang Khách thuê chính thức (Tenant) và đổi trạng thái WON.
     */
    public function test_convert_lead_to_tenant_success(): void
    {
        $sale = $this->createSales('Sales Chốt Deal', 'SALE-DEAL', 10);
        $lead = Lead::create([
            'contact_full_name' => 'Trần Văn Giám Đốc',
            'company_name' => 'Tập Đoàn Bất Động Sản Toàn Cầu',
            'phone' => '0912345678',
            'email' => 'ceo@global.vn',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $sale->id,
            'status' => LeadStatus::NEGOTIATING,
        ]);

        $response = $this->postJson("/api/v1/crm/leads/{$lead->id}/convert-to-tenant", [
            'tax_code' => '0312345678',
            'representative_position' => 'Tổng Giám Đốc',
            'headquarter_address' => 'Tầng 12, Bitexco, Q.1, TP.HCM',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.company_name', 'Tập Đoàn Bất Động Sản Toàn Cầu')
            ->assertJsonPath('data.tax_code', '0312345678');

        $lead->refresh();
        $this->assertEquals(LeadStatus::WON, $lead->status);
        $this->assertNotNull($lead->converted_tenant_id);
        $this->assertNotNull($lead->converted_at);

        $this->assertDatabaseHas('tenants', [
            'id' => $lead->converted_tenant_id,
            'tax_code' => '0312345678',
        ]);
    }
}
