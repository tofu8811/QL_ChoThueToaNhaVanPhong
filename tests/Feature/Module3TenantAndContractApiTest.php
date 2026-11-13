<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Module3TenantAndContractApiTest extends TestCase
{
    use RefreshDatabase;

    private Building $building;
    private Building $otherBuilding;
    private Unit $unitA;
    private Unit $unitB;
    private Tenant $tenant;
    private Tenant $otherTenant;
    private Admin $admin;
    private PropertyManager $pm;
    private PropertyManager $otherPm;
    private Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // Khởi tạo tòa nhà
        $this->building = Building::create([
            'building_code' => 'BLD-M3-01',
            'name' => 'Tòa nhà Landmark M3',
            'slug' => 'toa-nha-landmark-m3',
            'total_floors' => 20,
            'total_leasable_area_sqm' => 8000.00,
            'address' => '100 Nguyễn Huệ',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'grade' => 'A',
            'is_published' => true,
        ]);

        $this->otherBuilding = Building::create([
            'building_code' => 'BLD-M3-OTHER',
            'name' => 'Tòa nhà Sun Tower',
            'slug' => 'toa-nha-sun-tower',
            'total_floors' => 15,
            'total_leasable_area_sqm' => 6000.00,
            'address' => '200 Lê Lợi',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'grade' => 'B',
            'is_published' => true,
        ]);

        // Mặt bằng
        $this->unitA = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.801',
            'name' => 'Phòng 801 - View Sông',
            'floor_number' => 8,
            'area_sqm' => 150.00,
            'base_rental_price' => 600000,
            'handover_condition' => 'BASIC',
            'status' => UnitStatus::AVAILABLE,
        ]);

        $this->unitB = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.802',
            'name' => 'Phòng 802 - Góc Mặt Tiền',
            'floor_number' => 8,
            'area_sqm' => 200.00,
            'base_rental_price' => 650000,
            'handover_condition' => 'BASIC',
            'status' => UnitStatus::AVAILABLE,
        ]);

        // Khách thuê
        $this->tenant = Tenant::create([
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
            'status' => 'ACTIVE',
        ]);

        $this->otherTenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0002',
            'company_name' => 'Công ty Cổ Phần Gamma Global',
            'tax_code' => '0319999999',
            'business_license_private_path' => 'licenses/gamma.pdf',
            'representative_name' => 'Lê Thị Gamma',
            'representative_position' => 'Chủ Tịch HĐQT',
            'headquarter_address' => '200 Lê Lợi, Quận 1',
            'login_email' => 'login@gamma.vn',
            'billing_email' => 'accounting@gamma.vn',
            'contact_phone' => '0909999999',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        // Nhân sự
        $this->admin = Admin::create([
            'employee_code' => 'ADM-M3-01',
            'full_name' => 'Admin Hệ Thống',
            'email' => 'admin.m3@office.vn',
            'phone' => '0911000001',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->pm = PropertyManager::create([
            'employee_code' => 'PM-M3-01',
            'full_name' => 'BQL Phụ Trách Landmark',
            'email' => 'pm.m3@office.vn',
            'phone' => '0911000002',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        // Phân công PM cho $this->building
        $this->pm->buildings()->attach($this->building->id, [
            'is_primary' => true,
            'assigned_by_admin_id' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        $this->otherPm = PropertyManager::create([
            'employee_code' => 'PM-M3-OTHER',
            'full_name' => 'BQL Sun Tower',
            'email' => 'pm.other@office.vn',
            'phone' => '0911000003',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        // Phân công otherPm cho otherBuilding
        $this->otherPm->buildings()->attach($this->otherBuilding->id, [
            'is_primary' => true,
            'assigned_by_admin_id' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        $this->sale = Sale::create([
            'employee_code' => 'SALE-M3-01',
            'full_name' => 'Chuyên viên Sales M3',
            'email' => 'sale.m3@office.vn',
            'phone' => '0911000004',
            'password' => 'secret123',
            'commission_rate' => 1.5,
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * 1. Test CRUD Hồ sơ Khách thuê & Tìm kiếm
     */
    public function test_tenant_crud_and_search(): void
    {
        // Tạo mới Tenant
        $response = $this->postJson('/api/v1/admin/tenants', [
            'company_name' => 'Tập đoàn Đầu tư Delta',
            'tax_code' => '0317777777',
            'representative_name' => 'Võ Văn Delta',
            'headquarter_address' => '300 Hai Bà Trưng, Quận 3',
            'login_email' => 'contact@delta.vn',
            'billing_email' => 'finance@delta.vn',
            'contact_phone' => '0907777777',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.company_name', 'Tập đoàn Đầu tư Delta');

        $tenantId = $response->json('data.id');

        // Danh sách có bộ lọc tìm kiếm
        $listResponse = $this->getJson('/api/v1/admin/tenants?search=0317777777');
        $listResponse->assertStatus(200)
            ->assertJsonPath('data.0.tax_code', '0317777777');

        // Xem chi tiết
        $detailResponse = $this->getJson("/api/v1/admin/tenants/{$tenantId}");
        $detailResponse->assertStatus(200)
            ->assertJsonPath('data.id', $tenantId);

        // Cập nhật
        $updateResponse = $this->putJson("/api/v1/admin/tenants/{$tenantId}", [
            'representative_position' => 'Tổng Giám Đốc',
            'billing_email' => 'accounting.new@delta.vn',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.representative_position', 'Tổng Giám Đốc');

        // Vô hiệu hóa
        $deleteResponse = $this->deleteJson("/api/v1/admin/tenants/{$tenantId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseHas('tenants', ['id' => $tenantId, 'status' => 'LOCKED']);
    }

    /**
     * 2. Test Tải lên Giấy phép ĐKKD & Lấy Signed URL
     */
    public function test_tenant_license_upload_and_download_signed_url(): void
    {
        $fakeFile = UploadedFile::fake()->create('giay_phep_kinh_doanh.pdf', 500, 'application/pdf');

        $uploadResponse = $this->postJson("/api/v1/admin/tenants/{$this->tenant->id}/upload-license", [
            'file' => $fakeFile,
        ]);

        $uploadResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $storedPath = $uploadResponse->json('data.path');
        Storage::disk('local')->assertExists($storedPath);

        // Lấy Signed URL tải file
        $downloadResponse = $this->getJson("/api/v1/admin/tenants/{$this->tenant->id}/download-license");
        $downloadResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['download_url', 'expires_in_minutes']]);
    }

    /**
     * 3. Test Tạo Hợp đồng nháp và Gắn Mặt bằng (với Khóa bi quan)
     */
    public function test_contract_draft_creation_and_attach_unit(): void
    {
        $response = $this->postJson('/api/v1/admin/contracts', [
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-06-01',
            'end_date' => '2027-05-31',
            'deposit_amount' => 180000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'DRAFT');

        $contractId = $response->json('data.id');

        // Lấy danh sách hợp đồng (STT 43)
        $listContractsRes = $this->getJson('/api/v1/admin/contracts');
        $listContractsRes->assertStatus(200);

        // Xem chi tiết hợp đồng (STT 45)
        $detailContractRes = $this->getJson("/api/v1/admin/contracts/{$contractId}");
        $detailContractRes->assertStatus(200)
            ->assertJsonPath('data.id', $contractId);

        // Cập nhật điều khoản hợp đồng (STT 46)
        $updateContractRes = $this->putJson("/api/v1/admin/contracts/{$contractId}", [
            'deposit_amount' => 190000000,
        ]);
        $updateContractRes->assertStatus(200);

        // Gắn mặt bằng P.801 vào hợp đồng (STT 48)
        $attachResponse = $this->postJson("/api/v1/admin/contracts/{$contractId}/units", [
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 600000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        $attachResponse->assertStatus(201)
            ->assertJsonPath('data.contract.total_leased_area_sqm', '150.00');

        $this->assertDatabaseHas('contract_units', [
            'contract_id' => $contractId,
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
        ]);

        // Tải lên file scan hợp đồng (STT 53)
        Storage::fake('public');
        $uploadScanRes = $this->postJson("/api/v1/admin/contracts/{$contractId}/upload-files", [
            'files' => [
                UploadedFile::fake()->create('contract_signed.pdf', 200, 'application/pdf'),
            ],
            'document_type' => 'SIGNED_CONTRACT_SCAN',
        ]);
        $uploadScanRes->assertStatus(201);
        $fileId = $uploadScanRes->json('data.0.id');

        // Tải xuống file scan hợp đồng (STT 54)
        if ($fileId) {
            $downloadRes = $this->getJson("/api/v1/admin/contracts/{$contractId}/files/{$fileId}/download");
            $downloadRes->assertStatus(200);
        }

        // Gỡ mặt bằng khỏi hợp đồng nháp (STT 49)
        $detachRes = $this->deleteJson("/api/v1/admin/contracts/{$contractId}/units/{$this->unitA->id}");
        $detachRes->assertStatus(200);

        // Xóa hợp đồng bản nháp (STT 47)
        $deleteContractRes = $this->deleteJson("/api/v1/admin/contracts/{$contractId}");
        $deleteContractRes->assertStatus(200);
    }

    /**
     * 4. Test Thuật toán Chống Trùng Lịch Thuê Mặt Bằng (Anti-Lease Overlap)
     */
    public function test_anti_lease_overlap_algorithm_blocks_concurrent_lease(): void
    {
        // 1. Tạo Hợp đồng A và gán Unit A từ 01/01/2026 đến 31/12/2026
        $contractA = Contract::create([
            'contract_number' => 'HD-2026-OVERLAP-A',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'total_leased_area_sqm' => 150.00,
            'deposit_amount' => 100000000,
            'status' => ContractStatus::ACTIVE,
        ]);

        $contractA->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 600000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        // 2. Tạo Hợp đồng B nháp cho khoảng thời gian giao thoa (01/06/2026 đến 01/06/2027)
        $contractB = Contract::create([
            'contract_number' => 'HD-2026-OVERLAP-B',
            'building_id' => $this->building->id,
            'tenant_id' => $this->otherTenant->id,
            'start_date' => '2026-06-01',
            'end_date' => '2027-06-01',
            'total_leased_area_sqm' => 0.00,
            'deposit_amount' => 100000000,
            'status' => ContractStatus::DRAFT,
        ]);

        // 3. Cố tình gán Unit A vào Hợp đồng B -> BẮT BUỘC BỊ CHẶN VỚI MÃ HTTP 422
        $overlapResponse = $this->postJson("/api/v1/admin/contracts/{$contractB->id}/units", [
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 600000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        $overlapResponse->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['message', 'errors' => ['conflicting_contract_number']]);
    }

    /**
     * 5. Test Lập Hợp đồng Giữ chỗ (Booking) & Tự động đổi trạng thái phòng RESERVED
     */
    public function test_booking_contract_locks_unit_to_reserved(): void
    {
        $response = $this->postJson('/api/v1/admin/contracts/reserve', [
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'booking_deposit_amount' => 30000000,
            'reservation_expires_at' => now()->addDays(10)->toDateTimeString(),
            'units' => [
                [
                    'unit_id' => $this->unitB->id,
                    'leased_area_sqm' => 200.00,
                    'agreed_rental_price_per_sqm' => 650000,
                    'agreed_service_fee_per_sqm' => 50000,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'RESERVED');

        // Phòng Unit B phải chuyển sang RESERVED
        $this->assertEquals(UnitStatus::RESERVED, $this->unitB->fresh()->status);
    }

    /**
     * 6. Test Duyệt Ký Hợp đồng (Sign) & Kiểm tra Phân quyền Tòa nhà của PM
     */
    public function test_sign_contract_with_pm_building_scope_check(): void
    {
        $contract = Contract::create([
            'contract_number' => 'HD-TEST-SIGN-01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-08-01',
            'end_date' => '2027-08-01',
            'total_leased_area_sqm' => 150.00,
            'deposit_amount' => 100000000,
            'status' => ContractStatus::DRAFT,
        ]);
        $contract->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 600000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        // 6.1. PM không phụ trách tòa nhà (otherPm) duyệt ký -> BỊ TỪ CHỐI HTTP 403
        $this->actingAs($this->otherPm);
        $forbiddenResponse = $this->postJson("/api/v1/admin/contracts/{$contract->id}/sign");
        $forbiddenResponse->assertStatus(403);

        // 6.2. PM được phân công tòa nhà (pm) duyệt ký -> THÀNH CÔNG HTTP 200
        $this->actingAs($this->pm);
        $successResponse = $this->postJson("/api/v1/admin/contracts/{$contract->id}/sign");
        $successResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonPath('data.approved_by_pm_id', $this->pm->id);

        // Mặt bằng Unit A chuyển sang LEASED
        $this->assertEquals(UnitStatus::LEASED, $this->unitA->fresh()->status);
    }

    /**
     * 7. Test Gia hạn Hợp đồng (Renew) tạo bản ghi mới liên kết parent_contract_id
     */
    public function test_renew_contract_creates_new_record_linked_to_parent(): void
    {
        $oldContract = Contract::create([
            'contract_number' => 'HD-RENEW-BASE',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2024-01-01',
            'end_date' => '2026-01-01',
            'total_leased_area_sqm' => 150.00,
            'deposit_amount' => 90000000,
            'annual_price_escalation_percent' => 10.00,
            'status' => ContractStatus::ACTIVE,
        ]);
        $oldContract->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 600000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        $this->actingAs($this->admin);
        $renewResponse = $this->postJson("/api/v1/admin/contracts/{$oldContract->id}/renew", [
            'new_end_date' => '2028-01-01',
            'annual_price_escalation_percent' => 10.00,
        ]);

        $renewResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonPath('data.parent_contract_id', $oldContract->id);

        // Hợp đồng cũ phải chuyển sang RENEWED
        $this->assertEquals(ContractStatus::RENEWED, $oldContract->fresh()->status);

        // Đơn giá thuê chu kỳ mới được tăng 10% (600,000 * 1.1 = 660,000)
        $newContractId = $renewResponse->json('data.id');
        $newUnit = Contract::find($newContractId)->contractUnits()->first();
        $this->assertEquals(660000.00, (float) $newUnit->agreed_rental_price_per_sqm);
    }

    /**
     * 8. Test Thanh lý Hợp đồng & Nghiệm thu Trả phòng (Terminate)
     */
    public function test_terminate_contract_releases_units_and_validates_deposit(): void
    {
        $contract = Contract::create([
            'contract_number' => 'HD-TERM-01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2025-01-01',
            'end_date' => '2026-01-01',
            'total_leased_area_sqm' => 150.00,
            'deposit_amount' => 100000000,
            'status' => ContractStatus::ACTIVE,
        ]);
        $contract->contractUnits()->create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unitA->id,
            'leased_area_sqm' => 150.00,
            'agreed_rental_price_per_sqm' => 600000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);
        $this->unitA->update(['status' => UnitStatus::LEASED]);

        $this->actingAs($this->admin);

        // 8.1. Cố tình nhập hoàn cọc + phạt vượt quá số tiền cọc -> BỊ CHẶN HTTP 422
        $invalidDepositResponse = $this->postJson("/api/v1/admin/contracts/{$contract->id}/terminate", [
            'refund_deposit_amount' => 80000000,
            'penalty_deposit_amount' => 40000000, // 80 + 40 = 120tr > 100tr
            'termination_note' => 'Thanh lý sai tiền cọc',
        ]);
        $invalidDepositResponse->assertStatus(422);

        // 8.2. Nhập đúng phân định cọc -> THÀNH CÔNG
        $validResponse = $this->postJson("/api/v1/admin/contracts/{$contract->id}/terminate", [
            'refund_deposit_amount' => 70000000,
            'penalty_deposit_amount' => 30000000,
            'termination_note' => 'Khách dọn trả mặt bằng đúng hiện trạng',
        ]);

        $validResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'TERMINATED')
            ->assertJsonPath('data.refund_deposit_amount', '70000000.00')
            ->assertJsonPath('data.penalty_deposit_amount', '30000000.00');

        // Phòng Unit A được tự động trả về AVAILABLE
        $this->assertEquals(UnitStatus::AVAILABLE, $this->unitA->fresh()->status);
    }

    /**
     * 9. Test Cổng thông tin Khách thuê (Tenant Portal) & Phòng chống IDOR
     */
    public function test_tenant_portal_profile_and_contract_isolation(): void
    {
        // 1. Khách thuê Beta xem hồ sơ của mình
        $this->actingAs($this->tenant, 'tenant');
        $profileResponse = $this->getJson('/api/v1/tenant/profile');
        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.tax_code', $this->tenant->tax_code);

        // 2. Khách thuê Beta cập nhật email thanh toán
        $updateProfileResponse = $this->putJson('/api/v1/tenant/profile', [
            'billing_email' => 'ketoan.moi@beta.vn',
            'contact_phone' => '0901112233',
        ]);
        $updateProfileResponse->assertStatus(200)
            ->assertJsonPath('data.billing_email', 'ketoan.moi@beta.vn');

        // 3. Tạo hợp đồng cho Tenant Beta và Tenant Gamma
        $contractBeta = Contract::create([
            'contract_number' => 'HD-PORTAL-BETA',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-01-01',
            'total_leased_area_sqm' => 150.00,
            'deposit_amount' => 50000000,
            'status' => ContractStatus::ACTIVE,
        ]);

        $contractGamma = Contract::create([
            'contract_number' => 'HD-PORTAL-GAMMA',
            'building_id' => $this->building->id,
            'tenant_id' => $this->otherTenant->id,
            'start_date' => '2026-01-01',
            'end_date' => '2027-01-01',
            'total_leased_area_sqm' => 200.00,
            'deposit_amount' => 80000000,
            'status' => ContractStatus::ACTIVE,
        ]);

        // 4. Khách thuê Beta xem danh sách hợp đồng -> Chỉ thấy hợp đồng của mình
        $contractsListResponse = $this->getJson('/api/v1/tenant/contracts');
        $contractsListResponse->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.contract_number', 'HD-PORTAL-BETA');

        // 5. Khách thuê Beta xem chi tiết hợp đồng của mình -> 200 OK
        $detailBetaResponse = $this->getJson("/api/v1/tenant/contracts/{$contractBeta->id}");
        $detailBetaResponse->assertStatus(200);

        // 6. Khách thuê Beta cố tình xem hợp đồng của Gamma -> BỊ TỪ CHỐI HTTP 403 (IDOR Protection)
        $idorAttackResponse = $this->getJson("/api/v1/tenant/contracts/{$contractGamma->id}");
        $idorAttackResponse->assertStatus(403);
    }
}
