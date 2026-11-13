<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Livewire\ContractManager;
use App\Livewire\TenantManager;
use App\Livewire\TenantPortalWorkspace;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\ContractUnit;
use App\Models\DebitNote;
use App\Models\DebitNoteDetail;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Module3LivewireComponentTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Building $building;
    protected Unit $unit1;
    protected Unit $unit2;
    protected Tenant $tenant;
    protected Admin $admin;
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
            'building_code' => 'B-MOD3-01',
            'name' => 'Vietcombank Tower Saigon',
            'slug' => 'vietcombank-tower-saigon',
            'address' => '05 Công Trường Mê Linh',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 35,
            'total_leasable_area_sqm' => 50000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 20000,
            'default_service_fee_per_sqm' => 150000,
            'min_rent_price_per_sqm' => 1000000,
            'grade' => 'A',
            'is_published' => true,
        ]);

        $this->unit1 = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.1201',
            'name' => 'Văn phòng P.1201',
            'floor_number' => 12,
            'area_sqm' => 250,
            'base_rental_price' => 1100000,
            'status' => 'AVAILABLE',
        ]);

        $this->unit2 = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.1202',
            'name' => 'Văn phòng P.1202',
            'floor_number' => 12,
            'area_sqm' => 180,
            'base_rental_price' => 1150000,
            'status' => 'AVAILABLE',
        ]);

        $this->admin = Admin::create([
            'full_name' => 'Nguyễn Quản Trị Hệ Thống',
            'email' => 'admin@office.vn',
            'password' => bcrypt('password123'),
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->sale = Sale::create([
            'employee_code' => 'SALE-MOD3-01',
            'full_name' => 'Trần Kinh Doanh',
            'email' => 'sales@office.vn',
            'phone' => '0988776655',
            'password' => bcrypt('password123'),
            'commission_rate' => 2.0,
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-001',
            'company_name' => 'CÔNG TY TNHH CÔNG NGHỆ ALPHA',
            'tax_code' => '0315998877',
            'business_license_private_path' => 'licenses/alpha.pdf',
            'representative_name' => 'Nguyễn Văn Alpha',
            'representative_position' => 'Tổng Giám Đốc',
            'headquarter_address' => 'Số 10 Lê Thánh Tôn, Bến Nghé, Quận 1',
            'login_email' => 'contact@alpha.com.vn',
            'billing_email' => 'accounting@alpha.com.vn',
            'contact_phone' => '0903112233',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_contract_manager_component_renders_successfully(): void
    {
        $contract = Contract::create([
            'contract_number' => 'HD-VCB-2026-001',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 825000000,
            'status' => 'ACTIVE',
        ]);

        ContractUnit::create([
            'contract_id' => $contract->id,
            'unit_id' => $this->unit1->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 250,
            'agreed_rental_price_per_sqm' => 1100000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        Livewire::test(ContractManager::class)
            ->assertStatus(200)
            ->assertSee('HD-VCB-2026-001')
            ->assertSee('CÔNG TY TNHH CÔNG NGHỆ ALPHA')
            ->assertSee('Vietcombank Tower Saigon')
            ->assertSee('ACTIVE');
    }

    public function test_contract_manager_filter_and_search(): void
    {
        Contract::create([
            'contract_number' => 'HD-AAA-01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 500000000,
            'status' => 'DRAFT',
        ]);

        Contract::create([
            'contract_number' => 'HD-BBB-02',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 180,
            'deposit_amount' => 400000000,
            'status' => 'ACTIVE',
        ]);

        Livewire::test(ContractManager::class)
            ->set('searchKeyword', 'HD-AAA')
            ->assertSee('HD-AAA-01')
            ->assertDontSee('HD-BBB-02')
            ->set('searchKeyword', '')
            ->set('selectedStatus', 'ACTIVE')
            ->assertSee('HD-BBB-02')
            ->assertDontSee('HD-AAA-01');
    }

    public function test_contract_manager_validation_rules_when_creating(): void
    {
        Livewire::test(ContractManager::class)
            ->call('openCreateModal')
            ->call('saveContract')
            ->assertHasErrors(['building_id', 'tenant_id', 'selectedUnits']);
    }

    public function test_contract_manager_creates_contract_successfully(): void
    {
        Livewire::test(ContractManager::class)
            ->call('openCreateModal')
            ->set('building_id', $this->building->id)
            ->set('tenant_id', $this->tenant->id)
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2028-05-31')
            ->set('selectedUnitToAdd', $this->unit1->id)
            ->call('addUnit')
            ->assertCount('selectedUnits', 1)
            ->call('saveContract')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('contracts', [
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'status' => 'DRAFT',
        ]);
    }

    public function test_contract_manager_anti_overlap_validation(): void
    {
        // 1. Tạo hợp đồng đang hiệu lực thuê unit1 từ 2026-01-01 đến 2027-12-31
        $activeContract = Contract::create([
            'contract_number' => 'HD-ACTIVE-01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 800000000,
            'status' => 'ACTIVE',
        ]);

        ContractUnit::create([
            'contract_id' => $activeContract->id,
            'unit_id' => $this->unit1->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 250,
            'agreed_rental_price_per_sqm' => 1100000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        // 2. Thử gán unit1 vào hợp đồng mới trong khoảng trùng thời gian (2026-06-01 đến 2027-06-01)
        $test = Livewire::test(ContractManager::class)
            ->call('openCreateModal')
            ->set('building_id', $this->building->id)
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2027-06-01')
            ->set('selectedUnitToAdd', $this->unit1->id)
            ->call('addUnit');

        $this->assertNotNull($test->get('overlapWarning'));
        $this->assertCount(0, $test->get('selectedUnits'));
    }

    public function test_contract_manager_sign_contract_activates_and_marks_units_leased(): void
    {
        $this->actingAs($this->admin);

        $draft = Contract::create([
            'contract_number' => 'HD-DRAFT-SIGN',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 825000000,
            'status' => 'DRAFT',
        ]);

        ContractUnit::create([
            'contract_id' => $draft->id,
            'unit_id' => $this->unit1->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 250,
            'agreed_rental_price_per_sqm' => 1100000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        Livewire::test(ContractManager::class)
            ->call('signContract', $draft->id)
            ->assertSet('flashType', 'success');

        $this->assertEquals(ContractStatus::ACTIVE, $draft->fresh()->status);
        $this->assertEquals(UnitStatus::LEASED, $this->unit1->fresh()->status);
    }

    public function test_contract_manager_renew_contract(): void
    {
        $this->actingAs($this->admin);

        $activeContract = Contract::create([
            'contract_number' => 'HD-RENEW-BASE',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 825000000,
            'annual_price_escalation_percent' => 5.0,
            'status' => 'ACTIVE',
        ]);

        ContractUnit::create([
            'contract_id' => $activeContract->id,
            'unit_id' => $this->unit1->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 250,
            'agreed_rental_price_per_sqm' => 1100000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        Livewire::test(ContractManager::class)
            ->call('openRenewModal', $activeContract->id)
            ->set('new_end_date', '2028-12-31')
            ->set('renew_escalation_percent', 7.0)
            ->call('confirmRenew')
            ->assertSet('flashType', 'success')
            ->assertSet('showRenewModal', false);

        $this->assertEquals(ContractStatus::RENEWED, $activeContract->fresh()->status);
        $this->assertDatabaseHas('contracts', [
            'parent_contract_id' => $activeContract->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_contract_manager_terminate_contract_frees_units_to_available(): void
    {
        $this->actingAs($this->admin);

        $this->unit1->update(['status' => UnitStatus::LEASED]);

        $activeContract = Contract::create([
            'contract_number' => 'HD-TERMINATE-01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 825000000,
            'status' => 'ACTIVE',
        ]);

        ContractUnit::create([
            'contract_id' => $activeContract->id,
            'unit_id' => $this->unit1->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 250,
            'agreed_rental_price_per_sqm' => 1100000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        Livewire::test(ContractManager::class)
            ->call('openTerminateModal', $activeContract->id)
            ->set('refund_deposit_amount', 700000000)
            ->set('penalty_deposit_amount', 125000000)
            ->set('termination_note', 'Khách trả mặt bằng đúng hạn bàn giao.')
            ->call('confirmTerminate')
            ->assertSet('flashType', 'success')
            ->assertSet('showTerminateModal', false);

        $this->assertEquals(ContractStatus::TERMINATED, $activeContract->fresh()->status);
        $this->assertEquals(UnitStatus::AVAILABLE, $this->unit1->fresh()->status);
    }

    public function test_tenant_manager_renders_and_creates_tenant(): void
    {
        Livewire::test(TenantManager::class)
            ->assertStatus(200)
            ->assertSee('CÔNG TY TNHH CÔNG NGHỆ ALPHA')
            ->assertSee('0315998877')
            ->call('openCreateModal')
            ->set('company_name', 'CÔNG TY TNHH BETA TESTING')
            ->set('tax_code', '0399887766')
            ->set('representative_name', 'Trần Văn Beta')
            ->set('headquarter_address', 'Số 50 Nam Kỳ Khởi Nghĩa, Quận 1')
            ->set('login_email', 'contact@beta.com.vn')
            ->set('billing_email', 'accounting@beta.com.vn')
            ->set('contact_phone', '0911223344')
            ->call('saveTenant')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('tenants', [
            'company_name' => 'CÔNG TY TNHH BETA TESTING',
            'tax_code' => '0399887766',
        ]);
    }

    public function test_tenant_manager_prevents_deactivating_tenant_with_active_contract(): void
    {
        Contract::create([
            'contract_number' => 'HD-ACTIVE-PREVENT',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 825000000,
            'status' => 'ACTIVE',
        ]);

        Livewire::test(TenantManager::class)
            ->call('deactivateTenant', $this->tenant->id)
            ->assertSet('flashType', 'danger');

        $this->assertEquals('ACTIVE', $this->tenant->fresh()->status);
    }

    public function test_tenant_portal_workspace_renders_correctly(): void
    {
        $contract = Contract::create([
            'contract_number' => 'HD-PORTAL-01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2028-12-31',
            'total_leased_area_sqm' => 250,
            'deposit_amount' => 900000000,
            'payment_cycle_months' => 1,
            'payment_due_day' => 5,
            'status' => 'ACTIVE',
        ]);

        ContractUnit::create([
            'contract_id' => $contract->id,
            'unit_id' => $this->unit1->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 250,
            'agreed_rental_price_per_sqm' => 1100000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        $accountant = \App\Models\Accountant::create([
            'employee_code' => 'ACC-TEST-01',
            'full_name' => 'Nguyễn Kế Toán',
            'email' => 'ketoan@office.vn',
            'phone' => '0912345678',
            'password' => bcrypt('password123'),
            'status' => 'ACTIVE',
        ]);

        $debitNote = DebitNote::create([
            'debit_note_code' => 'DN-202611-001',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'contract_id' => $contract->id,
            'created_by_accountant_id' => $accountant->id,
            'billing_year' => 2026,
            'billing_month' => 11,
            'due_date' => '2026-11-15',
            'sub_total_amount' => 275000000,
            'vat_percent' => 10,
            'previous_debt_amount' => 0,
            'late_fee_amount' => 0,
            'total_amount' => 302500000,
            'paid_amount' => 0,
            'status' => \App\Enums\DebitNoteStatus::ISSUED,
            'is_immutable' => false,
        ]);

        DebitNoteDetail::create([
            'debit_note_id' => $debitNote->id,
            'unit_id' => $this->unit1->id,
            'item_category' => 'RENT',
            'description' => 'Tiền thuê tháng 11/2026 sàn P.1201',
            'quantity' => 250,
            'unit_name' => 'm²',
            'unit_price' => 1100000,
            'line_amount' => 275000000,
        ]);

        Livewire::test(TenantPortalWorkspace::class, ['tenantId' => $this->tenant->id])
            ->assertStatus(200)
            ->assertSee('CÔNG TY TNHH CÔNG NGHỆ ALPHA')
            ->assertSee('HD-PORTAL-01')
            ->assertSee('250')
            ->call('switchTab', 'bills')
            ->assertSee('DN-202611-001')
            ->assertSee('302.500.000')
            ->call('openDebitModal', $debitNote->id)
            ->assertSee('RENT');
    }

    public function test_ssr_module3_pages_render_http_200(): void
    {
        $this->get(route('contracts.page'))
            ->assertStatus(200)
            ->assertSee('Quản lý hợp đồng thuê');

        $this->get(route('tenants.page'))
            ->assertStatus(200)
            ->assertSee('Hồ sơ khách thuê doanh nghiệp');

        $this->get(route('tenant-portal.page'))
            ->assertStatus(200)
            ->assertSee('Cổng khách thuê văn phòng');
    }
}
