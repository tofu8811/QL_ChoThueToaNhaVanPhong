<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Events\LeadAssignedToSalesEvent;
use App\Events\LeadPendingAssignmentEvent;
use App\Livewire\CrmLeadPipeline;
use App\Livewire\LeadQuotationForm;
use App\Models\Building;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class Module2LivewireComponentTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Building $building;
    protected Unit $unit;
    protected Sale $sale;

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
            'building_code' => 'B-MOD2-01',
            'name' => 'Deutsches Haus Ho Chi Minh City',
            'slug' => 'deutsches-haus-hcmc',
            'address' => '33 Lê Duẩn',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 25,
            'total_leasable_area_sqm' => 30000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 20000,
            'default_service_fee_per_sqm' => 160000,
            'min_rent_price_per_sqm' => 1200000,
            'grade' => 'A',
            'is_published' => true,
        ]);

        $this->unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.1802',
            'name' => 'Văn phòng P.1802',
            'floor_number' => 18,
            'area_sqm' => 280,
            'base_rental_price' => 1300000,
            'status' => 'AVAILABLE',
        ]);

        $this->sale = Sale::create([
            'employee_code' => 'SALE-MOD2-01',
            'full_name' => 'Nguyễn Minh Quân',
            'email' => 'quan.sale@office.vn',
            'phone' => '0909123456',
            'password' => bcrypt('password123'),
            'commission_rate' => 2.0,
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);

        $this->sale->buildings()->attach($this->building->id, [
            'is_active' => true,
        ]);
    }

    /**
     * Test LeadQuotationForm component render thành công.
     */
    public function test_lead_quotation_form_renders_successfully(): void
    {
        Livewire::test(LeadQuotationForm::class)
            ->assertStatus(200)
            ->assertSee('Đăng Ký Nhận Báo Giá')
            ->assertSee('Họ và tên người liên hệ')
            ->assertSee('Deutsches Haus');
    }

    /**
     * Test LeadQuotationForm validate bắt buộc các trường cần thiết.
     */
    public function test_lead_quotation_form_validation_rules(): void
    {
        Livewire::test(LeadQuotationForm::class)
            ->call('submit')
            ->assertHasErrors([
                'contact_full_name',
                'phone',
                'email',
                'building_id',
            ]);
    }

    /**
     * Test khách hàng gửi form báo giá thành công và tạo Lead trong database.
     */
    public function test_lead_quotation_form_submits_successfully(): void
    {
        Event::fake([LeadAssignedToSalesEvent::class]);

        Livewire::test(LeadQuotationForm::class)
            ->set('contact_full_name', 'Trần Hữu Nam')
            ->set('phone', '0987654321')
            ->set('email', 'nam.tran@company.com')
            ->set('company_name', 'Công ty TNHH Nam Việt')
            ->set('building_id', $this->building->id)
            ->set('desired_area_sqm', 250)
            ->set('customer_note', 'Cần tư vấn sàn tầng cao view đẹp')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('isSubmitted', true)
            ->assertSet('resultType', 'success')
            ->assertSee('Tiếp Nhận Báo Giá Thành Công');

        $this->assertDatabaseHas('leads', [
            'contact_full_name' => 'Trần Hữu Nam',
            'phone' => '0987654321',
            'company_name' => 'Công ty TNHH Nam Việt',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $this->sale->id,
            'status' => LeadStatus::NEW->value,
        ]);
    }

    /**
     * Test cơ chế chống trùng lặp 30 ngày: gửi lại form thì nối note chứ không nhân bản Lead.
     */
    public function test_lead_quotation_form_handles_anti_duplicate_30_days(): void
    {
        Event::fake([LeadAssignedToSalesEvent::class]);

        // Lần 1: Gửi thành công
        Livewire::test(LeadQuotationForm::class)
            ->set('contact_full_name', 'Lê Thu Trang')
            ->set('phone', '0933222111')
            ->set('email', 'trang@logistics.vn')
            ->set('building_id', $this->building->id)
            ->call('submit')
            ->assertSet('isSubmitted', true)
            ->assertSet('resultType', 'success');

        $this->assertEquals(1, Lead::where('phone', '0933222111')->count());

        // Lần 2: Gửi lại cùng SĐT và tòa nhà trong 30 ngày
        Livewire::test(LeadQuotationForm::class)
            ->set('contact_full_name', 'Lê Thu Trang')
            ->set('phone', '0933222111')
            ->set('email', 'trang@logistics.vn')
            ->set('building_id', $this->building->id)
            ->set('customer_note', 'Yêu cầu thêm thông tin bãi đỗ xe')
            ->call('submit')
            ->assertSet('isSubmitted', true)
            ->assertSet('resultType', 'info')
            ->assertSee('Hồ Sơ Của Quý Khách Đã Được Cập Nhật');

        // Số lượng Lead vẫn chỉ là 1
        $this->assertEquals(1, Lead::where('phone', '0933222111')->count());

        // Phải có Activity Note ghi nhận tương tác mới
        $lead = Lead::where('phone', '0933222111')->first();
        $this->assertNotNull($lead);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'NOTE',
        ]);
    }

    /**
     * Test CrmLeadPipeline render thành công với chế độ Kanban và Table.
     */
    public function test_crm_lead_pipeline_component_renders(): void
    {
        Lead::create([
            'contact_full_name' => 'Ngô Bảo Châu',
            'phone' => '0901000001',
            'email' => 'chau@univ.edu.vn',
            'building_id' => $this->building->id,
            'status' => LeadStatus::NEW,
        ]);

        Livewire::test(CrmLeadPipeline::class)
            ->assertStatus(200)
            ->assertSee('Quản Lý Phễu Bán Hàng')
            ->assertSee('1. TIẾP NHẬN LEAD')
            ->assertSee('Ngô Bảo Châu')
            ->call('setViewMode', 'table')
            ->assertSet('viewMode', 'table')
            ->assertSee('Danh Sách Bảng')
            ->assertSee('Ngô Bảo Châu');
    }

    /**
     * Test chuyển đổi trạng thái Lead và ràng buộc lý do khi LOST.
     */
    public function test_crm_lead_pipeline_status_transition_and_lost_validation(): void
    {
        $lead = Lead::create([
            'contact_full_name' => 'Đỗ Hoàng Nam',
            'phone' => '0901000002',
            'email' => 'nam@fintech.com',
            'building_id' => $this->building->id,
            'status' => LeadStatus::NEW,
        ]);

        // Chuyển sang NEGOTIATING
        Livewire::test(CrmLeadPipeline::class)
            ->call('updateStatus', (string) $lead->id, 'NEGOTIATING');

        $this->assertEquals(LeadStatus::NEGOTIATING, $lead->fresh()->status);
        $this->assertNotNull($lead->fresh()->first_contacted_at);

        // Chuyển sang LOST -> mở modal
        $component = Livewire::test(CrmLeadPipeline::class)
            ->call('updateStatus', (string) $lead->id, 'LOST')
            ->assertSet('showLostModal', true)
            ->assertSet('lostLeadId', (string) $lead->id);

        // Thử submit thiếu lý do -> validate error
        $component->call('confirmLost')
            ->assertHasErrors(['lostCategory', 'lostDetail']);

        // Submit đầy đủ lý do -> thành công
        $component->set('lostCategory', 'Giá thuê vượt ngân sách')
            ->set('lostDetail', 'Khách chỉ có ngân sách tối đa $30/m2 nên không chốt được')
            ->call('confirmLost')
            ->assertHasNoErrors()
            ->assertSet('showLostModal', false);

        $this->assertEquals(LeadStatus::LOST, $lead->fresh()->status);
        $this->assertEquals('Giá thuê vượt ngân sách', $lead->fresh()->lost_reason_category);
    }

    /**
     * Test mở Detail Drawer và thêm tương tác mới vào Timeline.
     */
    public function test_crm_lead_pipeline_activity_timeline(): void
    {
        $lead = Lead::create([
            'contact_full_name' => 'Vũ Hải Yến',
            'phone' => '0901000003',
            'email' => 'yen@agency.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $this->sale->id,
            'status' => LeadStatus::NEW,
        ]);

        Livewire::test(CrmLeadPipeline::class)
            ->call('openDetail', (string) $lead->id)
            ->assertSet('showDetailDrawer', true)
            ->assertSet('selectedLeadId', (string) $lead->id)
            ->set('newActivityType', 'CALL')
            ->set('newActivityTitle', 'Gọi điện thoại tư vấn giá')
            ->set('newActivityContent', 'Khách hàng có thiện chí thuê 200m2 từ tháng tới')
            ->call('addActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'CALL',
            'title' => 'Gọi điện thoại tư vấn giá',
        ]);
    }

    /**
     * Test điều phối / gán Lead cho Sales khác.
     */
    public function test_crm_lead_pipeline_assign_sales(): void
    {
        Event::fake([LeadAssignedToSalesEvent::class]);

        $saleB = Sale::create([
            'employee_code' => 'SALE-MOD2-02',
            'full_name' => 'Phạm Quỳnh Chi',
            'email' => 'chi.sale@office.vn',
            'phone' => '0909654321',
            'password' => bcrypt('password123'),
            'commission_rate' => 2.0,
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);

        $lead = Lead::create([
            'contact_full_name' => 'Lý Hùng',
            'phone' => '0901000004',
            'email' => 'hung@cinema.vn',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $this->sale->id,
            'status' => LeadStatus::NEW,
        ]);

        Livewire::test(CrmLeadPipeline::class)
            ->call('openAssign', (string) $lead->id)
            ->assertSet('showAssignModal', true)
            ->set('targetSalesId', (string) $saleB->id)
            ->set('assignNote', 'Chuyển giao khách cho Sale B do bận đi công tác')
            ->call('submitAssign')
            ->assertHasNoErrors()
            ->assertSet('showAssignModal', false);

        $this->assertEquals($saleB->id, $lead->fresh()->assigned_sales_id);
    }

    /**
     * Test chuyển đổi Lead sang Khách thuê chính thức (Convert to Tenant).
     */
    public function test_crm_lead_pipeline_convert_to_tenant(): void
    {
        $lead = Lead::create([
            'contact_full_name' => 'Mai Phương Thúy',
            'company_name' => 'Tập Đoàn Bất Động Sản Sen Vàng',
            'phone' => '0901000005',
            'email' => 'thuy@senvang.com',
            'building_id' => $this->building->id,
            'assigned_sales_id' => $this->sale->id,
            'status' => LeadStatus::NEGOTIATING,
        ]);

        Livewire::test(CrmLeadPipeline::class)
            ->call('openConvert', (string) $lead->id)
            ->assertSet('showConvertModal', true)
            ->set('tenantTaxCode', '0319876543')
            ->set('tenantCompanyName', 'Tập Đoàn Bất Động Sản Sen Vàng')
            ->set('tenantRepName', 'Mai Phương Thúy')
            ->set('tenantEmail', 'thuy@senvang.com')
            ->set('tenantPhone', '0901000005')
            ->call('submitConvert')
            ->assertHasNoErrors()
            ->assertSet('showConvertModal', false);

        $this->assertEquals(LeadStatus::WON, $lead->fresh()->status);
        $this->assertNotNull($lead->fresh()->converted_tenant_id);

        $this->assertDatabaseHas('tenants', [
            'id' => $lead->fresh()->converted_tenant_id,
            'tax_code' => '0319876543',
            'company_name' => 'Tập Đoàn Bất Động Sản Sen Vàng',
        ]);
    }

    /**
     * Test tạo Lead thủ công qua hotline.
     */
    public function test_crm_lead_pipeline_manual_create_lead(): void
    {
        Event::fake([LeadAssignedToSalesEvent::class]);

        Livewire::test(CrmLeadPipeline::class)
            ->call('openCreateLead')
            ->assertSet('showCreateModal', true)
            ->set('manualContactName', 'Hoàng Văn Thụ')
            ->set('manualPhone', '0901000006')
            ->set('manualEmail', 'thu.hoang@export.com')
            ->set('manualBuildingId', (string) $this->building->id)
            ->set('manualAreaSqm', 180)
            ->call('submitCreateManual')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('leads', [
            'contact_full_name' => 'Hoàng Văn Thụ',
            'phone' => '0901000006',
            'lead_source' => 'HOTLINE',
        ]);
    }

    /**
     * Test route SSR /nhan-bao-gia hoạt động chuẩn SEO.
     */
    public function test_ssr_quote_page_renders_with_seo_and_livewire(): void
    {
        $response = $this->get(route('quote.page', ['building_id' => $this->building->id]));
        $response->assertStatus(200);
        $response->assertSee('Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát Văn Phòng TP.HCM');
        $response->assertSee('Họ và tên người liên hệ');
        $response->assertSee('Deutsches Haus');
    }
}
