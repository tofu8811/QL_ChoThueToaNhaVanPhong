<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Kiểm thử toàn diện chức năng tạo mặt bằng mới cho một tòa nhà đã thêm từ trước.
 * Bao gồm Happy Path, Ràng buộc nghiệp vụ (Invariants), Biên & Xác thực (Boundaries),
 * Tự động đồng bộ giá (Auto Sync Price) và Phân quyền (RBAC).
 */
class UnitCreationComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Admin $admin;
    protected PropertyManager $pm;
    protected Sale $sale;
    protected Accountant $accountant;

    protected string $adminToken;
    protected string $pmToken;
    protected string $saleToken;
    protected string $accountantToken;

    protected Building $building;
    protected Building $secondBuilding;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Khởi tạo địa giới hành chính
        $this->province = Province::create([
            'code' => 79,
            'name' => 'Thành phố Hồ Chí Minh',
            'slug' => 'thanh-pho-ho-chi-minh',
            'division_type' => 'thành phố trung ương',
            'codename' => 'ho_chi_minh',
            'phone_code' => 28,
        ]);

        // 2. Khởi tạo các tài khoản phân quyền
        $this->admin = Admin::create([
            'employee_code' => 'ADM-U-01',
            'full_name' => 'Admin Unit Tester',
            'email' => 'admin.unit@officeleasing.vn',
            'phone' => '0901000001',
            'password' => 'secret123',
            'role' => 'admin',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);
        $this->adminToken = $this->admin->createToken('admin-token', ['admin'])->plainTextToken;

        $this->pm = PropertyManager::create([
            'employee_code' => 'PM-U-01',
            'full_name' => 'PM Unit Tester',
            'email' => 'pm.unit@officeleasing.vn',
            'phone' => '0902000002',
            'password' => 'secret123',
            'role' => 'pm',
            'status' => 'ACTIVE',
        ]);
        $this->pmToken = $this->pm->createToken('pm-token', ['pm'])->plainTextToken;

        $this->sale = Sale::create([
            'employee_code' => 'SAL-U-01',
            'full_name' => 'Sale Unit Tester',
            'email' => 'sale.unit@officeleasing.vn',
            'phone' => '0903000003',
            'password' => 'secret123',
            'role' => 'sales',
            'status' => 'ACTIVE',
        ]);
        $this->saleToken = $this->sale->createToken('sale-token', ['sales'])->plainTextToken;

        $this->accountant = Accountant::create([
            'employee_code' => 'ACC-U-01',
            'full_name' => 'Accountant Unit Tester',
            'email' => 'acc.unit@officeleasing.vn',
            'phone' => '0904000004',
            'password' => 'secret123',
            'role' => 'accountant',
            'status' => 'ACTIVE',
        ]);
        $this->accountantToken = $this->accountant->createToken('acc-token', ['accountant'])->plainTextToken;

        // 3. Khởi tạo 2 tòa nhà đã có từ trước trong DB
        $this->building = Building::create([
            'building_code' => 'BLD-TEST-0001',
            'name' => 'Tòa Nhà Thử Nghiệm Alpha',
            'slug' => 'toa-nha-thu-nghiem-alpha',
            'province_code' => 79,
            'province' => 'Thành phố Hồ Chí Minh',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'address' => '100 Nguyễn Huệ',
            'grade' => 'A',
            'total_floors' => 25,
            'total_basements' => 3,
            'total_leasable_area_sqm' => 20000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 25000,
            'default_service_fee_per_sqm' => 120000,
            'min_rent_price_per_sqm' => 0.00,
            'max_rent_price_per_sqm' => 0.00,
            'is_published' => true,
        ]);

        $this->secondBuilding = Building::create([
            'building_code' => 'BLD-TEST-0002',
            'name' => 'Tòa Nhà Thử Nghiệm Beta',
            'slug' => 'toa-nha-thu-nghiem-beta',
            'province_code' => 79,
            'province' => 'Thành phố Hồ Chí Minh',
            'district' => 'Bình Thạnh',
            'ward' => 'Phường 22',
            'address' => '200 Điện Biên Phủ',
            'grade' => 'B',
            'total_floors' => 15,
            'total_leasable_area_sqm' => 10000,
            'electricity_unit_price' => 4000,
            'water_unit_price' => 22000,
            'default_service_fee_per_sqm' => 90000,
            'min_rent_price_per_sqm' => 0.00,
            'max_rent_price_per_sqm' => 0.00,
            'is_published' => true,
        ]);
    }

    // =========================================================================
    // NHÓM 1: HAPPY PATH & TỰ ĐỘNG SINH DỮ LIỆU MẶC ĐỊNH
    // =========================================================================

    /**
     * TC01: Tạo mặt bằng thành công với đầy đủ tất cả các trường thông tin.
     */
    public function test_tc01_create_unit_with_full_valid_payload_success(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-1501',
            'name' => 'Văn Phòng Hạng Sang Tầng 15',
            'floor_number' => 15,
            'area_sqm' => 280.5,
            'base_rental_price' => 650000,
            'handover_condition' => 'FULL_FURNISHED',
            'direction' => 'Đông Nam',
            'floor_plan_image_url' => 'https://storage.officeleasing.vn/units/alp-1501-layout.png',
            'available_from_date' => '2026-12-01',
            'status' => 'AVAILABLE',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.unit_code', 'ALP-1501')
            ->assertJsonPath('data.name', 'Văn Phòng Hạng Sang Tầng 15')
            ->assertJsonPath('data.floor_number', 15)
            ->assertJsonPath('data.area_sqm', '280.50')
            ->assertJsonPath('data.base_rental_price', '650000.00')
            ->assertJsonPath('data.status', 'AVAILABLE');

        $this->assertDatabaseHas('units', [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-1501',
            'handover_condition' => 'FULL_FURNISHED',
            'direction' => 'Đông Nam',
        ]);
    }

    /**
     * TC02: Tạo mặt bằng chỉ với các trường tối thiểu, kiểm tra dữ liệu mặc định tự sinh:
     * - name tự sinh: "Văn phòng {unit_code}"
     * - status tự sinh: AVAILABLE
     * - last_status_changed_at được thiết lập thời gian hiện tại
     */
    public function test_tc02_create_unit_with_minimal_fields_and_defaults(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-0801',
            'floor_number' => 8,
            'area_sqm' => 150.0,
            'base_rental_price' => 450000,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Văn phòng ALP-0801')
            ->assertJsonPath('data.status', 'AVAILABLE');

        $createdUnit = Unit::where('unit_code', 'ALP-0801')->first();
        $this->assertNotNull($createdUnit);
        $this->assertEquals('Văn phòng ALP-0801', $createdUnit->name);
        $this->assertEquals(UnitStatus::AVAILABLE, $createdUnit->status);
        $this->assertNotNull($createdUnit->last_status_changed_at);
    }

    /**
     * TC03: Tự động đồng bộ khoảng giá thuê (min_rent_price_per_sqm & max_rent_price_per_sqm)
     * của tòa nhà cha mỗi khi tạo thêm mặt bằng mới có đơn giá khác nhau.
     */
    public function test_tc03_auto_sync_building_rent_price_range(): void
    {
        $this->assertEquals(0, (float) $this->building->min_rent_price_per_sqm);
        $this->assertEquals(0, (float) $this->building->max_rent_price_per_sqm);

        // Tạo phòng 1: Giá 300,000 -> Tòa nhà: min 300k, max 300k
        $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-PRICE-01',
                'floor_number' => 3,
                'area_sqm' => 100,
                'base_rental_price' => 300000,
            ])->assertStatus(201);

        $this->building->refresh();
        $this->assertEquals(300000, (float) $this->building->min_rent_price_per_sqm);
        $this->assertEquals(300000, (float) $this->building->max_rent_price_per_sqm);

        // Tạo phòng 2: Giá 750,000 -> Tòa nhà: min 300k, max 750k
        $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-PRICE-02',
                'floor_number' => 7,
                'area_sqm' => 200,
                'base_rental_price' => 750000,
            ])->assertStatus(201);

        $this->building->refresh();
        $this->assertEquals(300000, (float) $this->building->min_rent_price_per_sqm);
        $this->assertEquals(750000, (float) $this->building->max_rent_price_per_sqm);

        // Tạo phòng 3: Giá 180,000 -> Tòa nhà: min 180k, max 750k
        $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-PRICE-03',
                'floor_number' => 2,
                'area_sqm' => 80,
                'base_rental_price' => 180000,
            ])->assertStatus(201);

        $this->building->refresh();
        $this->assertEquals(180000, (float) $this->building->min_rent_price_per_sqm);
        $this->assertEquals(750000, (float) $this->building->max_rent_price_per_sqm);
    }

    /**
     * TC04: Composite Uniqueness: Hai tòa nhà KHÁC NHAU được phép có cùng mã mặt bằng (ví dụ P.101).
     */
    public function test_tc04_composite_uniqueness_allows_same_unit_code_in_different_buildings(): void
    {
        $commonUnitCode = 'P.101';

        // Tạo phòng P.101 ở Tòa nhà Alpha -> Thành công
        $resA = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => $commonUnitCode,
                'floor_number' => 1,
                'area_sqm' => 120,
                'base_rental_price' => 400000,
            ]);
        $resA->assertStatus(201);

        // Tạo phòng P.101 ở Tòa nhà Beta -> Thành công
        $resB = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->secondBuilding->id,
                'unit_code' => $commonUnitCode,
                'floor_number' => 1,
                'area_sqm' => 150,
                'base_rental_price' => 380000,
            ]);
        $resB->assertStatus(201);

        $this->assertDatabaseCount('units', 2);
    }

    /**
     * TC05: Biên tầng: Cho phép tạo mặt bằng tại tầng hầm (floor_number mang giá trị âm).
     */
    public function test_tc05_create_unit_in_basement_floor_with_negative_floor_number(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'B1-KHO01',
            'name' => 'Kho Lưu Trữ Tầng Hầm B1',
            'floor_number' => -1,
            'area_sqm' => 85.0,
            'base_rental_price' => 150000,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.floor_number', -1)
            ->assertJsonPath('data.unit_code', 'B1-KHO01');
    }

    /**
     * TC06: Chuẩn hóa dữ liệu đầu vào: Hỗ trợ alias từ Modal Form JS (area -> area_sqm, rent_price_per_sqm -> base_rental_price).
     */
    public function test_tc06_field_normalization_supports_frontend_modal_aliases(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-NORM-01',
            'floor_number' => 5,
            'area' => 175.5, // Alias từ JS form
            'rent_price_per_sqm' => 520000, // Alias từ JS form
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.area_sqm', '175.50')
            ->assertJsonPath('data.base_rental_price', '520000.00');

        $this->assertDatabaseHas('units', [
            'unit_code' => 'ALP-NORM-01',
            'area_sqm' => 175.50,
            'base_rental_price' => 520000.00,
        ]);
    }

    // =========================================================================
    // NHÓM 2: NEGATIVE TESTS & RÀNG BUỘC TOÀN VẸN NGHIỆP VỤ (INVARIANTS)
    // =========================================================================

    /**
     * TC07: Chặn trùng mã mặt bằng trong CÙNG một tòa nhà (HTTP 422 BusinessException).
     */
    public function test_tc07_fails_when_duplicate_unit_code_in_same_building(): void
    {
        // 1. Tạo phòng lần đầu
        Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-DUP-01',
            'name' => 'Văn phòng ALP-DUP-01',
            'floor_number' => 10,
            'area_sqm' => 100,
            'base_rental_price' => 500000,
            'status' => UnitStatus::AVAILABLE,
        ]);

        // 2. Thử tạo lại đúng mã ALP-DUP-01 trong tòa nhà này
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-DUP-01',
                'floor_number' => 10,
                'area_sqm' => 120,
                'base_rental_price' => 550000,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertStringContainsString('Mã mặt bằng [ALP-DUP-01] đã tồn tại trong tòa nhà này.', $response->json('message'));
    }

    /**
     * TC08: Chặn trùng mã với mặt bằng ĐÃ BỊ XÓA MỀM (Soft Deleted) trong cùng tòa nhà.
     */
    public function test_tc08_fails_when_duplicate_unit_code_with_soft_deleted_unit(): void
    {
        // 1. Tạo phòng và sau đó xóa mềm
        $unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-SOFTDEL-01',
            'name' => 'Văn phòng ALP-SOFTDEL-01',
            'floor_number' => 11,
            'area_sqm' => 90,
            'base_rental_price' => 480000,
            'status' => UnitStatus::AVAILABLE,
        ]);
        $unit->delete();
        $this->assertSoftDeleted('units', ['id' => $unit->id]);

        // 2. Thử tạo mới với mã phòng của bản ghi đã xóa mềm
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-SOFTDEL-01',
                'floor_number' => 11,
                'area_sqm' => 95,
                'base_rental_price' => 500000,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertStringContainsString('Mã mặt bằng [ALP-SOFTDEL-01] đã tồn tại trong tòa nhà này.', $response->json('message'));
    }

    /**
     * TC09: Chặn tạo mặt bằng khi building_id không tồn tại trên hệ thống.
     */
    public function test_tc09_fails_when_building_id_does_not_exist(): void
    {
        $fakeBuildingId = (string) Str::uuid();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $fakeBuildingId,
                'unit_code' => 'FAKE-01',
                'floor_number' => 1,
                'area_sqm' => 100,
                'base_rental_price' => 300000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['building_id']);

        $this->assertStringContainsString('Tòa nhà đã chọn không tồn tại trên hệ thống.', $response->json('errors.building_id.0'));
    }

    /**
     * TC10: Chặn tạo mặt bằng cho một tòa nhà ĐÃ BỊ XÓA MỀM (Soft Deleted Building).
     */
    public function test_tc10_fails_when_building_is_soft_deleted(): void
    {
        // Xóa mềm tòa nhà thứ 2
        $this->secondBuilding->delete();
        $this->assertSoftDeleted('buildings', ['id' => $this->secondBuilding->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->secondBuilding->id,
                'unit_code' => 'BETA-DEAD-01',
                'floor_number' => 2,
                'area_sqm' => 100,
                'base_rental_price' => 300000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['building_id']);
    }

    // =========================================================================
    // NHÓM 3: VALIDATION & BOUNDARY TESTS (GIÁ TRỊ BIÊN & RÀNG BUỘC DỮ LIỆU)
    // =========================================================================

    /**
     * TC11: Gửi payload rỗng -> Bắt lỗi validation tất cả 5 trường bắt buộc.
     */
    public function test_tc11_fails_validation_when_empty_payload(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'building_id',
                'unit_code',
                'floor_number',
                'area_sqm',
                'base_rental_price',
            ]);
    }

    /**
     * TC12: Ranh giới diện tích: diện tích = 0, diện tích < 0 hoặc không phải số.
     */
    public function test_tc12_fails_validation_when_area_is_zero_or_negative_or_non_numeric(): void
    {
        // 1. area = 0
        $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-01',
                'floor_number' => 1,
                'area_sqm' => 0,
                'base_rental_price' => 300000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['area_sqm']);

        // 2. area < 0
        $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-02',
                'floor_number' => 1,
                'area_sqm' => -50.5,
                'base_rental_price' => 300000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['area_sqm']);

        // 3. area là chuỗi chữ cái
        $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-03',
                'floor_number' => 1,
                'area_sqm' => 'chuoi_khong_hop_le',
                'base_rental_price' => 300000,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['area_sqm']);
    }

    /**
     * TC13: Ranh giới đơn giá: base_rental_price < 0 bị chặn.
     */
    public function test_tc13_fails_validation_when_base_rental_price_is_negative(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-04',
                'floor_number' => 1,
                'area_sqm' => 100,
                'base_rental_price' => -25000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['base_rental_price']);

        $this->assertStringContainsString('Đơn giá thuê cơ sở phải lớn hơn hoặc bằng 0.', $response->json('errors.base_rental_price.0'));
    }

    /**
     * TC14: Số tầng không phải số nguyên (float hoặc chuỗi).
     */
    public function test_tc14_fails_validation_when_floor_number_not_integer(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-05',
                'floor_number' => 'tang_muoi_hai',
                'area_sqm' => 100,
                'base_rental_price' => 300000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['floor_number']);
    }

    /**
     * TC15: Trạng thái không thuộc Enum UnitStatus.
     */
    public function test_tc15_fails_validation_when_status_is_invalid_enum(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-06',
                'floor_number' => 2,
                'area_sqm' => 100,
                'base_rental_price' => 300000,
                'status' => 'TRANG_THAI_KHONG_TON_TAI',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * TC16: Ngày bàn giao (available_from_date) sai định dạng ngày.
     */
    public function test_tc16_fails_validation_when_available_from_date_is_invalid(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => 'ALP-ERR-07',
                'floor_number' => 2,
                'area_sqm' => 100,
                'base_rental_price' => 300000,
                'available_from_date' => '31-02-2026', // Sai định dạng ngày
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['available_from_date']);
    }

    /**
     * TC17: Vượt quá giới hạn ký tự tối đa (unit_code > 50 ký tự, name > 255 ký tự).
     */
    public function test_tc17_fails_validation_when_string_lengths_exceed_limits(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'unit_code' => str_repeat('X', 51), // Vượt 50 ký tự
                'name' => str_repeat('Y', 256), // Vượt 255 ký tự
                'floor_number' => 2,
                'area_sqm' => 100,
                'base_rental_price' => 300000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_code', 'name']);
    }

    // =========================================================================
    // NHÓM 4: BẢO MẬT & PHÂN QUYỀN (RBAC & ACCESS CONTROL)
    // =========================================================================

    /**
     * TC18: Yêu cầu chưa đăng nhập (không gửi Token) -> Chặn 401 Unauthorized.
     */
    public function test_tc18_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/admin/units', [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-GUEST',
            'floor_number' => 1,
            'area_sqm' => 100,
            'base_rental_price' => 300000,
        ]);

        $response->assertStatus(401);
    }

    /**
     * TC19: Các vai trò không có quyền tạo mặt bằng (Sales, Kế toán) -> Chặn 403 Forbidden.
     * Chỉ Admin và Property Manager mới có quyền tạo mặt bằng.
     */
    public function test_tc19_unauthorized_roles_cannot_create_units(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-NO-PERM',
            'floor_number' => 1,
            'area_sqm' => 100,
            'base_rental_price' => 300000,
        ];

        // 1. Chuyên viên Sales gửi request
        $resSale = $this->withHeader('Authorization', 'Bearer ' . $this->saleToken)
            ->postJson('/api/v1/admin/units', $payload);
        $resSale->assertStatus(403);

        // 2. Kế toán tòa nhà gửi request
        $resAccountant = $this->withHeader('Authorization', 'Bearer ' . $this->accountantToken)
            ->postJson('/api/v1/admin/units', $payload);
        $resAccountant->assertStatus(403);
    }

    /**
     * TC20: Ban quản lý tòa nhà (Property Manager) tạo mặt bằng thành công
     * và trường updated_by_pm_id được tự động gán chính xác bằng ID của PM đó.
     */
    public function test_tc20_property_manager_can_create_unit_and_pm_id_is_recorded(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-PM-01',
            'name' => 'Văn Phòng Do PM Tạo',
            'floor_number' => 6,
            'area_sqm' => 210,
            'base_rental_price' => 580000,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->pmToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.unit_code', 'ALP-PM-01')
            ->assertJsonPath('data.updated_by_pm_id', $this->pm->id);

        $this->assertDatabaseHas('units', [
            'unit_code' => 'ALP-PM-01',
            'updated_by_pm_id' => $this->pm->id,
        ]);
    }

    // =========================================================================
    // NHÓM 6: TỰ ĐỘNG SINH MÃ SÀN & KIỂM TRA TẦNG PHÙ HỢP VỚI TÒA NHÀ
    // =========================================================================

    /**
     * TC21: Tự động sinh mã sàn (unit_code) khi người dùng không truyền unit_code:
     * - Tầng 7: Tự sinh mã dạng L07-01 hoặc ALP-0701
     * - Tiếp tục tạo phòng thứ 2 trên tầng 7: Tự sinh mã tiếp theo (sequence 02)
     */
    public function test_tc21_auto_generates_unit_code_when_omitted(): void
    {
        // 1. Tạo phòng đầu tiên trên tầng 7 không truyền unit_code
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'floor_number' => 7,
                'area_sqm' => 180,
                'base_rental_price' => 500000,
            ]);

        $response1->assertStatus(201);
        $code1 = $response1->json('data.unit_code');
        $this->assertNotEmpty($code1);
        $this->assertTrue(str_contains($code1, '0701') || str_contains($code1, 'L07-01'));

        // 2. Tạo phòng thứ hai trên tầng 7 không truyền unit_code -> kỳ vọng sequence tăng lên 02
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'floor_number' => 7,
                'area_sqm' => 220,
                'base_rental_price' => 520000,
            ]);

        $response2->assertStatus(201);
        $code2 = $response2->json('data.unit_code');
        $this->assertNotEmpty($code2);
        $this->assertNotEquals($code1, $code2);
        $this->assertTrue(str_contains($code2, '0702') || str_contains($code2, 'L07-02'));
    }

    /**
     * TC22: Chặn tạo mặt bằng khi vị trí tầng vượt quá số tầng của tòa nhà:
     * Tòa nhà Alpha có total_floors = 25. Thử tạo tầng 26 -> 422 lỗi.
     */
    public function test_tc22_fails_when_floor_exceeds_building_total_floors(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'floor_number' => 26, // Vượt quá 25
                'area_sqm' => 150,
                'base_rental_price' => 450000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['floor_number']);

        $errorMsg = $response->json('errors.floor_number.0');
        $this->assertStringContainsString('vượt quá số tầng của tòa nhà', $errorMsg);
    }

    /**
     * TC23: Chặn tạo mặt bằng tại tầng 0 (không tồn tại trong kiến trúc tòa nhà).
     */
    public function test_tc23_fails_when_floor_number_is_zero(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'floor_number' => 0,
                'area_sqm' => 150,
                'base_rental_price' => 450000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['floor_number']);

        $errorMsg = $response->json('errors.floor_number.0');
        $this->assertStringContainsString('không có tầng 0', $errorMsg);
    }

    /**
     * TC24: Chặn tạo mặt bằng tại tầng hầm vượt quá số lượng tầng hầm của tòa nhà:
     * Tòa nhà Alpha có total_basements = 3. Thử tạo tầng hầm -4 -> 422 lỗi.
     */
    public function test_tc24_fails_when_basement_floor_exceeds_building_total_basements(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $this->building->id,
                'floor_number' => -4, // Vượt quá 3 hầm
                'area_sqm' => 100,
                'base_rental_price' => 200000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['floor_number']);

        $errorMsg = $response->json('errors.floor_number.0');
        $this->assertStringContainsString('vượt quá số tầng hầm', $errorMsg);
    }

    /**
     * TC25: Chặn tạo mặt bằng tầng hầm khi tòa nhà không có tầng hầm (total_basements = 0).
     */
    public function test_tc25_fails_when_basement_floor_requested_on_building_without_basements(): void
    {
        $noBasementBuilding = Building::create([
            'building_code' => 'BLD-NO-BASEMENT',
            'name' => 'Tòa Nhà Không Hầm',
            'slug' => 'toa-nha-khong-ham',
            'province_code' => 79,
            'province' => 'Thành phố Hồ Chí Minh',
            'district' => 'Quận 3',
            'ward' => 'Phường 6',
            'address' => '50 Võ Văn Tần',
            'grade' => 'C',
            'total_floors' => 5,
            'total_basements' => 0,
            'total_leasable_area_sqm' => 2000,
            'electricity_unit_price' => 4000,
            'water_unit_price' => 20000,
            'default_service_fee_per_sqm' => 50000,
            'min_rent_price_per_sqm' => 0.00,
            'max_rent_price_per_sqm' => 0.00,
            'is_published' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', [
                'building_id' => $noBasementBuilding->id,
                'floor_number' => -1,
                'area_sqm' => 80,
                'base_rental_price' => 150000,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['floor_number']);

        $errorMsg = $response->json('errors.floor_number.0');
        $this->assertStringContainsString('không có tầng hầm', $errorMsg);
    }

    /**
     * TC26: API gợi ý mã sàn (GET /api/v1/admin/units/suggest-code) trả về mã duy nhất hợp lệ.
     */
    public function test_tc26_suggest_code_api_endpoint_success(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/v1/admin/units/suggest-code?building_id={$this->building->id}&floor_number=18");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.floor_number', 18);

        $suggestedCode = $response->json('data.unit_code');
        $this->assertNotEmpty($suggestedCode);
    }

    /**
     * TC27: API gợi ý mã sàn trả về 422 khi tầng không hợp lệ (vượt quá số tầng tòa nhà).
     */
    public function test_tc27_suggest_code_api_endpoint_fails_when_floor_invalid(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/v1/admin/units/suggest-code?building_id={$this->building->id}&floor_number=50");

        $response->assertStatus(422);
    }

    /**
     * TC28: Tạo mặt bằng khi gửi handover_condition = null (từ form modal không chọn hiện trạng bàn giao).
     * Hệ thống tự động gán mặc định 'BASIC' để không vi phạm ràng buộc NOT NULL của CSDL.
     */
    public function test_tc28_create_unit_with_explicit_null_handover_condition_defaults_to_basic(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-NULL-HAND',
            'floor_number' => 10,
            'area_sqm' => 120.0,
            'base_rental_price' => 400000,
            'handover_condition' => null,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.handover_condition', 'BASIC');

        $createdUnit = Unit::where('unit_code', 'ALP-NULL-HAND')->first();
        $this->assertNotNull($createdUnit);
        $this->assertEquals('BASIC', $createdUnit->handover_condition);
    }

    /**
     * TC29: Tạo mặt bằng với handover_condition = 'FULL_FURNISHED' được chọn từ modal.
     */
    public function test_tc29_create_unit_with_custom_handover_condition_persists_correctly(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'unit_code' => 'ALP-FULL-FURN',
            'floor_number' => 12,
            'area_sqm' => 150.0,
            'base_rental_price' => 550000,
            'handover_condition' => 'FULL_FURNISHED',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/v1/admin/units', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.handover_condition', 'FULL_FURNISHED');

        $createdUnit = Unit::where('unit_code', 'ALP-FULL-FURN')->first();
        $this->assertNotNull($createdUnit);
        $this->assertEquals('FULL_FURNISHED', $createdUnit->handover_condition);
    }
}
