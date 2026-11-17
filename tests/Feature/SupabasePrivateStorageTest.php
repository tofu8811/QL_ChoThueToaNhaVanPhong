<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\ContractService;
use App\Services\TenantService;
use App\Livewire\ContractManager;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupabasePrivateStorageTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Building $building;
    protected Tenant $tenant;
    protected Contract $contract;
    protected ContractService $contractService;
    protected TenantService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contractService = app(ContractService::class);
        $this->tenantService = app(TenantService::class);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-STORAGE-01',
            'full_name' => 'Storage Tester',
            'email' => 'admin.storage@office.vn',
            'phone' => '0912000001',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->building = Building::create([
            'building_code' => 'BLD-STORAGE',
            'name' => 'Storage Test Tower',
            'slug' => 'storage-test-tower',
            'address' => '123 Private Storage St',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'grade' => 'A',
            'total_floors' => 10,
            'total_leasable_area_sqm' => 5000.00,
            'status' => 'ACTIVE',
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-STORAGE-01',
            'company_name' => 'Storage Test Corp',
            'tax_code' => '0319998888',
            'business_license_private_path' => 'licenses/sample.pdf',
            'representative_name' => 'Nguyen Van A',
            'representative_position' => 'CEO',
            'headquarter_address' => '123 Private St',
            'login_email' => 'storage@test.vn',
            'billing_email' => 'storage@test.vn',
            'contact_phone' => '0901234567',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $this->contract = Contract::create([
            'contract_number' => 'HD-STORAGE-001',
            'tenant_id' => $this->tenant->id,
            'building_id' => $this->building->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'total_leased_area_sqm' => 150.00,
            'total_rental_price' => 50000000,
            'deposit_amount' => 150000000,
            'status' => 'DRAFT',
            'contract_pdf_private_path' => null,
        ]);
    }

    /**
     * Kiểm tra upload file scan hợp đồng lên Private Storage (mock faked disk).
     */
    public function test_upload_contract_files_to_private_storage(): void
    {
        Storage::fake('supabase_private');
        config(['filesystems.disks.supabase_private.key' => 'fake_s3_key']);

        $fakePdf = UploadedFile::fake()->create('signed_contract.pdf', 300, 'application/pdf');

        $uploaded = $this->contractService->uploadContractFiles($this->contract, [$fakePdf], 'SIGNED_CONTRACT_SCAN');

        $this->assertCount(1, $uploaded);
        $record = $uploaded[0];

        $this->assertEquals('signed_contract.pdf', $record->file_name);
        $this->assertEquals('SIGNED_CONTRACT_SCAN', $record->document_type);
        $this->assertStringStartsWith("contracts/{$this->contract->id}/", $record->image_url);

        // File phải tồn tại trong private disk
        Storage::disk('supabase_private')->assertExists($record->image_url);

        // Hợp đồng chính tự động gán đường dẫn
        $this->contract->refresh();
        $this->assertEquals($record->image_url, $this->contract->contract_pdf_private_path);
    }

    /**
     * Kiểm tra sinh temporary signed URL cho file scan hợp đồng.
     */
    public function test_generate_contract_file_temporary_signed_url(): void
    {
        Storage::fake('supabase_private');
        config(['filesystems.disks.supabase_private.key' => 'fake_s3_key']);

        $fakePdf = UploadedFile::fake()->create('handover.pdf', 250, 'application/pdf');
        $uploaded = $this->contractService->uploadContractFiles($this->contract, [$fakePdf], 'HANDOVER_MINUTES');
        $fileId = $uploaded[0]->id;

        $url = $this->contractService->generateFileDownloadUrl($this->contract, $fileId, 15);

        $this->assertNotEmpty($url);
        $this->assertIsString($url);
    }

    /**
     * Kiểm tra upload Giấy phép kinh doanh lên Private Storage.
     */
    public function test_upload_tenant_business_license_to_private_storage(): void
    {
        Storage::fake('supabase_private');
        config(['filesystems.disks.supabase_private.key' => 'fake_s3_key']);

        $fakeLicense = UploadedFile::fake()->create('license_dkkd.pdf', 400, 'application/pdf');

        $path = $this->tenantService->uploadBusinessLicense($this->tenant, $fakeLicense);

        Storage::disk('supabase_private')->assertExists($path);
        $this->tenant->refresh();
        $this->assertEquals($path, $this->tenant->business_license_private_path);

        $downloadUrl = $this->tenantService->getLicenseDownloadUrl($this->tenant, 10);
        $this->assertNotEmpty($downloadUrl);
        $this->assertIsString($downloadUrl);
    }

    /**
     * Kiểm tra giao diện Livewire tạo hợp đồng mới kèm tệp ảnh / scan tải lên Private Storage.
     */
    public function test_livewire_contract_manager_creates_contract_with_attached_file(): void
    {
        Storage::fake('supabase_private');
        config(['filesystems.disks.supabase_private.key' => 'fake_s3_key']);

        $unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.1001',
            'name' => 'Phòng 1001 VIP',
            'floor_number' => 10,
            'area_sqm' => 120,
            'base_rental_price' => 500000,
            'status' => 'AVAILABLE',
        ]);

        $fakeContractScan = UploadedFile::fake()->image('scan_hop_dong_giao_dien.jpg', 800, 600);

        Livewire::test(ContractManager::class)
            ->call('openCreateModal')
            ->set('building_id', $this->building->id)
            ->set('tenant_id', $this->tenant->id)
            ->set('start_date', '2026-12-01')
            ->set('end_date', '2027-12-01')
            ->set('selectedUnitToAdd', $unit->id)
            ->call('addUnit')
            ->set('newContractFiles', [$fakeContractScan])
            ->call('saveContract')
            ->assertHasNoErrors()
            ->assertSee('Đã tạo bản nháp Hợp đồng thuê thành công');

        $createdContract = Contract::where('tenant_id', $this->tenant->id)
            ->where('id', '!=', $this->contract->id)
            ->first();

        $this->assertNotNull($createdContract);
        $this->assertNotNull($createdContract->contract_pdf_private_path);

        // Kiểm tra file được lưu vào private storage
        Storage::disk('supabase_private')->assertExists($createdContract->contract_pdf_private_path);

        // Kiểm tra mở modal chi tiết và sinh download url
        $image = $createdContract->images()->first();
        $this->assertNotNull($image);

        Livewire::test(ContractManager::class)
            ->call('openDetailModal', $createdContract->id)
            ->assertSee($createdContract->contract_number)
            ->assertSee('scan_hop_dong_giao_dien.jpg')
            ->call('generateDownloadUrl', $image->id)
            ->assertHasNoErrors();
    }
}
