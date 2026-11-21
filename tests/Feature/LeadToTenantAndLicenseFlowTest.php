<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\LeadStatus;
use App\Enums\UnitStatus;
use App\Livewire\TenantManager;
use App\Livewire\TenantPortalWorkspace;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\ContractService;
use App\Services\LeadService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Feature Test Suite: Luồng liên hoàn Lead -> Tenant -> Giấy phép ĐKKD -> Hợp đồng thuê -> Cấp tài khoản Portal.
 */
class LeadToTenantAndLicenseFlowTest extends TestCase
{
    use RefreshDatabase;

    private Building $building;
    private Unit $unit;
    private Sale $sales;
    private Admin $admin;
    private PropertyManager $pm;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        if (config('filesystems.disks.supabase_private.key')) {
            Storage::fake('supabase_private');
        }

        $this->building = Building::create([
            'building_code' => 'BLD-TEST-FLOW',
            'name' => 'Saigon Tower Test',
            'slug' => 'saigon-tower-test',
            'total_floors' => 25,
            'total_leasable_area_sqm' => 10000.00,
            'address' => '29 Lê Duẩn',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'grade' => 'A',
            'status' => 'PUBLISHED',
        ]);

        $this->unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'SGT-1201',
            'name' => 'Văn phòng Tầng 12 - SGT-1201',
            'floor_number' => 12,
            'area_sqm' => 200.00,
            'base_rental_price' => 800000.00,
            'status' => UnitStatus::AVAILABLE,
        ]);

        $this->sales = Sale::create([
            'employee_code' => 'SALES-TEST-01',
            'full_name' => 'Nguyễn Văn Sales',
            'email' => 'sales.test@saigontower.vn',
            'password' => Hash::make('password123'),
            'phone' => '0901112233',
            'commission_rate' => 1.5,
            'max_concurrent_leads' => 20,
            'status' => 'ACTIVE',
        ]);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-TEST-01',
            'full_name' => 'Admin Test',
            'email' => 'admin.test@saigontower.vn',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->pm = PropertyManager::create([
            'employee_code' => 'PM-TEST-01',
            'full_name' => 'PM Test',
            'email' => 'pm.test@saigontower.vn',
            'password' => Hash::make('password123'),
            'status' => 'ACTIVE',
        ]);
        $this->pm->buildings()->attach($this->building->id, ['is_primary' => true]);
    }

    /**
     * 1. Test chuyển đổi Lead sang Tenant thành công và tự động cấp thông tin tài khoản.
     */
    public function test_lead_converts_to_tenant_successfully_with_account_credentials(): void
    {
        $leadService = app(LeadService::class);

        // Tạo Lead mới ở trạng thái NEGOTIATING
        $lead = Lead::create([
            'building_id' => $this->building->id,
            'unit_id' => $this->unit->id,
            'assigned_sales_id' => $this->sales->id,
            'contact_full_name' => 'Trần Đình Trọng',
            'phone' => '0988776655',
            'email' => 'trong.tran@techcorp.vn',
            'company_name' => 'CÔNG TY TNHH CÔNG NGHỆ TECHCORP',
            'status' => LeadStatus::NEGOTIATING,
            'source' => 'WEBSITE',
        ]);

        $tenantData = [
            'tax_code' => '0316889999',
            'company_name' => 'CÔNG TY TNHH CÔNG NGHỆ TECHCORP',
            'representative_name' => 'Trần Đình Trọng',
            'representative_position' => 'Tổng Giám Đốc',
            'headquarter_address' => 'Tầng 12 Saigon Tower, 29 Lê Duẩn, Quận 1, TP.HCM',
            'login_email' => 'admin@techcorp.vn',
            'billing_email' => 'accounting@techcorp.vn',
            'contact_phone' => '0988776655',
            'password' => 'TechCorp@2026',
        ];

        $tenant = $leadService->convertToTenant($lead, $tenantData);

        // Assert Tenant
        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('0316889999', $tenant->tax_code);
        $this->assertEquals('ACTIVE', $tenant->status);
        $this->assertEquals($lead->id, $tenant->converted_from_lead_id);
        $this->assertEquals($this->sales->id, $tenant->created_by_sales_id);
        $this->assertTrue(Hash::check('TechCorp@2026', $tenant->password));

        // Assert Lead updated to WON
        $lead = $lead->fresh();
        $this->assertEquals(LeadStatus::WON, $lead->status);
        $this->assertEquals($tenant->id, $lead->converted_tenant_id);
        $this->assertNotNull($lead->converted_at);

        // Assert LeadActivity logged
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'NOTE',
            'title' => 'Chuyển đổi thành công sang Khách thuê chính thức',
        ]);
    }

    /**
     * 2. Test tải lên Giấy phép ĐKKD vào Private Storage và lưu Relative Path trong CSDL.
     */
    public function test_business_license_upload_to_private_storage(): void
    {
        $tenantService = app(TenantService::class);

        $tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0099',
            'company_name' => 'CÔNG TY CP GIẢI PHÁP SỐ TECHVIET',
            'tax_code' => '0315558888',
            'business_license_private_path' => 'licenses/default.pdf',
            'representative_name' => 'Nguyễn Minh Quân',
            'representative_position' => 'Giám Đốc',
            'headquarter_address' => 'Quận 1, TP.HCM',
            'login_email' => 'quan.nguyen@techviet.vn',
            'billing_email' => 'ke-toan@techviet.vn',
            'contact_phone' => '0912345678',
            'password' => 'Pass@123456',
            'status' => 'ACTIVE',
        ]);

        $fakePdf = UploadedFile::fake()->create('giay_phep_dkkd.pdf', 300, 'application/pdf');

        $storedPath = $tenantService->uploadBusinessLicense($tenant, $fakePdf);

        $this->assertStringStartsWith('licenses/', $storedPath);
        $this->assertEquals($storedPath, $tenant->fresh()->business_license_private_path);

        $disk = config('filesystems.disks.supabase_private.key') ? 'supabase_private' : 'local';
        Storage::disk($disk)->assertExists($storedPath);
    }

    /**
     * 3. Test sinh S3 Presigned Temporary URL có thời hạn cho Giấy phép ĐKKD.
     */
    public function test_business_license_signed_url_generation(): void
    {
        $tenantService = app(TenantService::class);

        $tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0100',
            'company_name' => 'CÔNG TY TNHH ĐẦU TƯ ALPHA',
            'tax_code' => '0319991111',
            'business_license_private_path' => 'licenses/default.pdf',
            'representative_name' => 'Võ Hoàng Yến',
            'representative_position' => 'Tổng Giám Đốc',
            'headquarter_address' => 'Bến Nghé, Quận 1',
            'login_email' => 'yen.vo@alpha.vn',
            'billing_email' => 'billing@alpha.vn',
            'contact_phone' => '0909999888',
            'password' => 'Pass@123456',
            'status' => 'ACTIVE',
        ]);

        $fakePdf = UploadedFile::fake()->create('dkkd_alpha.pdf', 250, 'application/pdf');
        $tenantService->uploadBusinessLicense($tenant, $fakePdf);

        $signedUrl = $tenantService->getLicenseDownloadUrl($tenant, 10);

        $this->assertNotEmpty($signedUrl);
        $this->assertIsString($signedUrl);
    }

    /**
     * 4. Test xác thực đăng nhập Cổng Khách Thuê với Guard tenant.
     */
    public function test_tenant_can_authenticate_and_access_tenant_portal(): void
    {
        $rawPassword = 'SecureTenantPass@2026';

        $tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0101',
            'company_name' => 'CÔNG TY TNHH PHẦN MỀM SAIGON',
            'tax_code' => '0318884444',
            'business_license_private_path' => 'licenses/default.pdf',
            'representative_name' => 'Đặng Đình Dũng',
            'representative_position' => 'Giám Đốc',
            'headquarter_address' => 'Saigon Tower, Quận 1',
            'login_email' => 'dung.dang@saigonsoftware.com',
            'billing_email' => 'billing@saigonsoftware.com',
            'contact_phone' => '0933334455',
            'password' => $rawPassword,
            'status' => 'ACTIVE',
        ]);

        // Kiểm tra attempt guard 'tenant'
        $loginSuccess = Auth::guard('tenant')->attempt([
            'login_email' => 'dung.dang@saigonsoftware.com',
            'password' => $rawPassword,
        ]);

        $this->assertTrue($loginSuccess);
        $this->assertEquals($tenant->id, Auth::guard('tenant')->user()->id);
    }

    /**
     * 5. Test luồng Hợp đồng thuê gắn với Tenant và hiển thị trên Cổng Khách Thuê.
     */
    public function test_tenant_with_active_contract_displays_in_tenant_portal(): void
    {
        $contractService = app(ContractService::class);

        $tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0102',
            'company_name' => 'TẬP ĐOÀN BÁN LẺ RETAILCORP',
            'tax_code' => '0317772222',
            'business_license_private_path' => 'licenses/default.pdf',
            'representative_name' => 'Phạm Thu Hương',
            'representative_position' => 'Chủ Tịch',
            'headquarter_address' => 'Quận 1, TP.HCM',
            'login_email' => 'huong.pham@retailcorp.vn',
            'billing_email' => 'billing@retailcorp.vn',
            'contact_phone' => '0977889900',
            'password' => 'Pass@123',
            'status' => 'ACTIVE',
        ]);

        // Tạo hợp đồng nháp cho Tenant này
        $contract = $contractService->createDraftContract([
            'contract_number' => 'HD-RETAILCORP-2026-001',
            'building_id' => $this->building->id,
            'tenant_id' => $tenant->id,
            'start_date' => '2026-06-01',
            'end_date' => '2029-05-31',
            'deposit_amount' => 480000000.00, // 3 tháng cọc
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'late_fee_interest_percent' => 0.05,
            'annual_price_escalation_percent' => 5.00,
            'units' => [
                [
                    'unit_id' => $this->unit->id,
                    'leased_area_sqm' => 200.00,
                    'agreed_rental_price_per_sqm' => 800000.00,
                    'agreed_service_fee_per_sqm' => 120000.00,
                ],
            ],
        ]);

        // Ký duyệt kích hoạt hợp đồng
        $contractService->signContract($contract, '2026-05-25', $this->admin->id, null);

        $this->assertEquals(ContractStatus::ACTIVE, $contract->fresh()->status);
        $this->assertEquals(UnitStatus::LEASED, $this->unit->fresh()->status);

        // Kiểm tra component TenantPortalWorkspace hiển thị văn phòng đang thuê
        Livewire::test(TenantPortalWorkspace::class, ['tenantId' => $tenant->id])
            ->assertSee('TẬP ĐOÀN BÁN LẺ RETAILCORP')
            ->assertSee('HD-RETAILCORP-2026-001')
            ->assertSee('200')
            ->assertSee('Saigon Tower Test');
    }

    /**
     * 6. Test Livewire TenantManager hỗ trợ upload và mở Giấy phép ĐKKD.
     */
    public function test_tenant_manager_livewire_license_actions(): void
    {
        $tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0103',
            'company_name' => 'CÔNG TY CP TRUYỀN THÔNG DIGICOM',
            'tax_code' => '0314443333',
            'business_license_private_path' => 'licenses/default.pdf',
            'representative_name' => 'Ngô Bảo Châu',
            'representative_position' => 'Giám Đốc',
            'headquarter_address' => 'Lê Duẩn, Quận 1',
            'login_email' => 'chau.ngo@digicom.vn',
            'billing_email' => 'billing@digicom.vn',
            'contact_phone' => '0901234999',
            'password' => 'Pass@123',
            'status' => 'ACTIVE',
        ]);

        $fakePdf = UploadedFile::fake()->create('giay_phep_digicom.pdf', 400, 'application/pdf');

        Livewire::test(TenantManager::class)
            ->call('openDetailModal', $tenant->id)
            ->assertSet('selectedTenant.id', $tenant->id)
            ->set('licenseFile', $fakePdf)
            ->call('uploadDetailLicense')
            ->assertHasNoErrors()
            ->assertSee('Tải lên Giấy phép ĐKKD cho doanh nghiệp CÔNG TY CP TRUYỀN THÔNG DIGICOM thành công!');

        $this->assertStringStartsWith('licenses/', $tenant->fresh()->business_license_private_path);
    }
}
