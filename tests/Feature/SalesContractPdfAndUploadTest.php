<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SalesContractPdfAndUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Sale $sale1;
    protected Sale $sale2;
    protected Building $building;
    protected Tenant $tenant;
    protected Unit $unit;
    protected Contract $draftContract;
    protected Contract $bookingContract;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        Storage::fake('public');

        $this->sale1 = Sale::create([
            'employee_code' => 'NV-SALE-001',
            'full_name' => 'Nguyễn Văn Sales 1',
            'email' => 'sale1@office.vn',
            'phone' => '0901111222',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $this->sale2 = Sale::create([
            'employee_code' => 'NV-SALE-002',
            'full_name' => 'Trần Thị Sales 2',
            'email' => 'sale2@office.vn',
            'phone' => '0903333444',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $this->building = Building::create([
            'building_code' => 'BLD-TEST',
            'name' => 'Tòa Nhà Saigon Centre',
            'slug' => 'toa-nha-saigon-centre',
            'address' => '65 Lê Lợi',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'Hồ Chí Minh',
            'grade' => 'A',
            'total_floors' => 25,
            'total_leasable_area_sqm' => 30000.00,
            'status' => 'ACTIVE',
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-001',
            'company_name' => 'CÔNG TY TNHH CÔNG NGHỆ ALPHA',
            'tax_code' => '0312345678',
            'representative_name' => 'Nguyễn Alpha',
            'representative_position' => 'Giám Đốc',
            'headquarter_address' => '123 Nguyễn Thị Minh Khai, Q.1',
            'login_email' => 'contact@alpha.com',
            'billing_email' => 'billing@alpha.com',
            'contact_phone' => '0988776655',
            'business_license_private_path' => 'licenses/alpha.pdf',
            'password' => 'secret123',
            'status' => 'ACTIVE',
            'created_by_sales_id' => $this->sale1->id,
        ]);

        $this->unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'SGC-1001',
            'name' => 'Văn Phòng Tầng 10',
            'floor_number' => 10,
            'area_sqm' => 200.00,
            'base_rental_price' => 1200000,
            'status' => 'AVAILABLE',
        ]);

        // Hợp đồng nháp DRAFT do Sales 1 tạo
        $this->draftContract = Contract::create([
            'contract_number' => 'HD-SGC-2026-001',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale1->id,
            'start_date' => '2026-12-01',
            'end_date' => '2028-12-01',
            'total_leased_area_sqm' => 200.00,
            'deposit_amount' => 720000000,
            'payment_cycle_months' => 1,
            'payment_due_day' => 5,
            'status' => ContractStatus::DRAFT,
        ]);

        $this->draftContract->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unit->id,
            'leased_area_sqm' => 200.00,
            'agreed_rental_price_per_sqm' => 1200000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);

        // Hợp đồng cọc giữ chỗ BOOKING do Sales 1 tạo
        $this->bookingContract = Contract::create([
            'contract_number' => 'HD-SGC-2026-BK1',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale1->id,
            'start_date' => '2026-12-01',
            'end_date' => '2028-12-01',
            'total_leased_area_sqm' => 200.00,
            'deposit_amount' => 720000000,
            'booking_deposit_amount' => 50000000,
            'reservation_expires_at' => '2026-12-10',
            'status' => ContractStatus::RESERVED,
        ]);

        $this->bookingContract->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unit->id,
            'leased_area_sqm' => 200.00,
            'agreed_rental_price_per_sqm' => 1200000,
            'agreed_service_fee_per_sqm' => 150000,
        ]);
    }

    /**
     * Test Sales có thể upload tệp chứng từ/scan vào hợp đồng DRAFT do mình tạo.
     */
    public function test_sales_can_upload_attachments_to_own_draft_contract(): void
    {
        Sanctum::actingAs($this->sale1, ['*']);

        $file = UploadedFile::fake()->create('loi_agreement.pdf', 500, 'application/pdf');

        $response = $this->postJson("/api/v1/admin/contracts/{$this->draftContract->id}/upload-files", [
            'files' => [$file],
            'document_type' => 'SIGNED_CONTRACT_SCAN',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Tải lên tài liệu hợp đồng thành công.',
        ]);

        $this->assertDatabaseHas('contract_images', [
            'contract_id' => $this->draftContract->id,
            'file_name' => 'loi_agreement.pdf',
            'document_type' => 'SIGNED_CONTRACT_SCAN',
        ]);
    }

    /**
     * Test Sales có thể upload ủy nhiệm chi (PAYMENT_RECEIPT) vào hợp đồng BOOKING giữ chỗ.
     */
    public function test_sales_can_upload_deposit_receipt_to_booking_contract(): void
    {
        Sanctum::actingAs($this->sale1, ['*']);

        $receipt = UploadedFile::fake()->image('unc_dat_coc.png', 800, 600);

        $response = $this->postJson("/api/v1/admin/contracts/{$this->bookingContract->id}/upload-files", [
            'files' => [$receipt],
            'document_type' => 'PAYMENT_RECEIPT',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('contract_images', [
            'contract_id' => $this->bookingContract->id,
            'file_name' => 'unc_dat_coc.png',
            'document_type' => 'PAYMENT_RECEIPT',
        ]);
    }

    /**
     * Test Sales khác (không phải người tạo hợp đồng) bị từ chối upload file (403).
     */
    public function test_other_sales_cannot_upload_files_to_contract(): void
    {
        Sanctum::actingAs($this->sale2, ['*']);

        $file = UploadedFile::fake()->create('unauthorized.pdf', 300, 'application/pdf');

        $response = $this->postJson("/api/v1/admin/contracts/{$this->draftContract->id}/upload-files", [
            'files' => [$file],
            'document_type' => 'SIGNED_CONTRACT_SCAN',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test Sales có thể xem trang in / xuất PDF hợp đồng DRAFT.
     */
    public function test_sales_can_view_draft_contract_print_page(): void
    {
        $response = $this->actingAs($this->sale1, 'admin')->get("/admin/contracts/{$this->draftContract->id}/print");

        $response->assertStatus(200);
        $response->assertSee('HỢP ĐỒNG THUÊ VĂN PHÒNG (BẢN DỰ THẢO)');
        $response->assertSee('[DỰ THẢO TRÌNH DUYỆT]');
        $response->assertSee($this->draftContract->contract_number);
        $response->assertSee('CÔNG TY TNHH CÔNG NGHỆ ALPHA');
        $response->assertSee('SGC-1001');
    }

    /**
     * Test Sales xem trang in / xuất PDF hợp đồng BOOKING (Hiển thị Thỏa thuận đặt cọc giữ chỗ).
     */
    public function test_sales_can_view_booking_contract_print_page(): void
    {
        $response = $this->actingAs($this->sale1, 'admin')->get("/admin/contracts/{$this->bookingContract->id}/print");

        $response->assertStatus(200);
        $response->assertSee('THỎA THUẬN ĐẶT CỌC GIỮ CHỖ MẶT BẰNG VĂN PHÒNG');
        $response->assertSee('[ĐẶT CỌC GIỮ CHỖ]');
        $response->assertSee($this->bookingContract->contract_number);
        $response->assertSee('50.000.000 VNĐ');
        $response->assertSee('Năm mươi triệu đồng chẵn');
        $response->assertSee('10/12/2026');
    }
}
