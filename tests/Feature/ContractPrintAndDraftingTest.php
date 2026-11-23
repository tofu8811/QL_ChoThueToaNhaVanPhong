<?php

namespace Tests\Feature;

use App\Helpers\NumberToWordsHelper;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\ContractService;
use App\Livewire\ContractManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractPrintAndDraftingTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Building $building;
    protected Tenant $tenant;
    protected Unit $unit;
    protected Contract $contract;
    protected ContractService $contractService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contractService = app(ContractService::class);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-PRINT-01',
            'full_name' => 'Nguyễn Quản Trị',
            'email' => 'admin.print@office.vn',
            'phone' => '0912345678',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->building = Building::create([
            'building_code' => 'BLD-NEXUS',
            'name' => 'Tòa Nhà The Nexus Tower',
            'slug' => 'toa-nha-the-nexus-tower',
            'address' => '3A Tôn Đức Thắng',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'Hồ Chí Minh',
            'grade' => 'A',
            'total_floors' => 35,
            'total_leasable_area_sqm' => 45000.00,
            'status' => 'ACTIVE',
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-VNG-01',
            'company_name' => 'CÔNG TY CỔ PHẦN VNG',
            'tax_code' => '0303588502',
            'business_license_private_path' => 'licenses/vng.pdf',
            'representative_name' => 'Lê Hồng Minh',
            'representative_position' => 'Chủ Tịch HĐQT',
            'headquarter_address' => 'Z06 Đường số 13, KCX Tân Thuận, Quận 7',
            'login_email' => 'contact@vng.com.vn',
            'billing_email' => 'finance@vng.com.vn',
            'contact_phone' => '02839623888',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $this->unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'NEX-0501',
            'name' => 'Sàn Văn Phòng Lầu 5',
            'floor_number' => 5,
            'area_sqm' => 150.00,
            'base_rental_price' => 1400000,
            'status' => 'AVAILABLE',
        ]);

        $this->contract = Contract::create([
            'contract_number' => 'HD-NEXUS-2026-001',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-11-20',
            'end_date' => '2028-11-20',
            'total_leased_area_sqm' => 150.00,
            'deposit_amount' => 630000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'late_fee_interest_percent' => 0.05,
            'annual_price_escalation_percent' => 5.00,
            'status' => 'DRAFT',
            'approved_by_admin_id' => $this->admin->id,
        ]);

        $this->contract->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unit->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 1400000,
            'agreed_service_fee_per_sqm' => 200000,
        ]);
    }

    /**
     * Test Helper chuyển đổi số tiền sang chữ tiếng Việt.
     */
    public function test_number_to_vietnamese_words(): void
    {
        $words1 = NumberToWordsHelper::toVietnameseWords(630000000);
        $this->assertStringContainsString('Sáu trăm ba mươi triệu đồng chẵn', $words1);

        $words2 = NumberToWordsHelper::toVietnameseWords(15450000);
        $this->assertStringContainsString('Mười lăm triệu bốn trăm năm mươi nghìn đồng chẵn', $words2);

        $wordsZero = NumberToWordsHelper::toVietnameseWords(0);
        $this->assertStringContainsString('Không đồng chẵn', $wordsZero);
    }

    /**
     * Test ContractService::getContractPrintData() nạp đầy đủ thông tin pháp lý.
     */
    public function test_contract_service_prepares_print_data_accurately(): void
    {
        $data = $this->contractService->getContractPrintData($this->contract->id);

        $this->assertArrayHasKey('contract', $data);
        $this->assertArrayHasKey('party_a', $data);
        $this->assertArrayHasKey('party_b', $data);

        // Bên A
        $this->assertStringContainsString('TÒA NHÀ THE NEXUS TOWER', $data['party_a']['company_name']);
        $this->assertEquals('Nguyễn Quản Trị', $data['party_a']['representative']);

        // Bên B
        $this->assertEquals('CÔNG TY CỔ PHẦN VNG', $data['party_b']['company_name']);
        $this->assertEquals('0303588502', $data['party_b']['tax_code']);
        $this->assertEquals('Lê Hồng Minh', $data['party_b']['representative']);

        // Tài chính
        // Tiền thuê: 150 * 1.400.000 = 210.000.000
        $this->assertEquals(210000000, $data['monthly_rent']);
        $this->assertStringContainsString('Hai trăm mười triệu đồng chẵn', $data['monthly_rent_words']);

        // Phí DV: 150 * 200.000 = 30.000.000
        $this->assertEquals(30000000, $data['monthly_service_fee']);
        $this->assertStringContainsString('Ba mươi triệu đồng chẵn', $data['monthly_service_fee_words']);

        // Tổng: 240.000.000
        $this->assertEquals(240000000, $data['monthly_total']);

        // Cọc: 630.000.000
        $this->assertEquals(630000000, $data['deposit_amount']);
        $this->assertStringContainsString('Sáu trăm ba mươi triệu đồng chẵn', $data['deposit_amount_words']);
    }

    /**
     * Test Route xuất PDF / In hợp đồng chuẩn A4: GET /admin/contracts/{id}/print.
     */
    public function test_print_contract_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get("/admin/contracts/{$this->contract->id}/print");

        $response->assertStatus(200);
        $response->assertSee('HỢP ĐỒNG THUÊ VĂN PHÒNG');
        $response->assertSee($this->contract->contract_number);
        $response->assertSee('CÔNG TY CỔ PHẦN VNG');
        $response->assertSee('NEX-0501');
        $response->assertSee('150.00');
        $response->assertSee('Sáu trăm ba mươi triệu đồng chẵn');
        $response->assertSee('Xuất PDF');
        $response->assertSee('triggerPrint');
    }

    /**
     * Test Route In hợp đồng chặn người dùng chưa đăng nhập.
     */
    public function test_print_contract_page_redirects_unauthenticated_user(): void
    {
        $response = $this->get("/admin/contracts/{$this->contract->id}/print");
        $response->assertRedirect('/?auth=required');
    }

    /**
     * Test Livewire ContractManager hiển thị nút Xuất PDF ở danh sách và chi tiết.
     */
    public function test_livewire_contract_manager_renders_pdf_export_buttons(): void
    {
        Livewire::test(ContractManager::class)
            ->assertSee('Xuất PDF')
            ->assertSee("/admin/contracts/{$this->contract->id}/print")
            ->call('openDetailModal', $this->contract->id)
            ->assertSee('Xuất PDF')
            ->assertSee($this->contract->contract_number);
    }
}
