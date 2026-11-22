<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bộ kiểm thử chuyên sâu nhằm xác minh tính chính xác của tầng FormRequest Validation
 * sau khi thực hiện Refactor (kiểm tra đầy đủ các ca lỗi biên 422, ca hợp lệ, kế thừa và chuẩn hóa input).
 */
class FormRequestValidationComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Sale $sale;
    protected Tenant $tenant;
    protected Building $building;
    protected Unit $unit;
    protected string $adminToken;
    protected string $tenantToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->sale = Sale::firstOrFail();
        $this->tenant = Tenant::firstOrFail();
        $this->building = Building::firstOrFail();
        $this->unit = Unit::where('building_id', $this->building->id)->firstOrFail();

        $this->adminToken = $this->admin->createToken('admin_test')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('tenant_test')->plainTextToken;
    }

    /**
     * 1. Kiểm tra StoreBuildingRequest: chặn khi payload rỗng hoặc vi phạm kiểu dữ liệu/enum.
     */
    public function test_store_building_validation_fails_on_empty_and_invalid_data(): void
    {
        // 1.1 Gửi payload rỗng -> Phải chặn 422 cho các trường required
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'building_code',
                'name',
                'address',
                'ward',
                'district',
                'province',
                'province_code',
                'total_floors',
                'total_leasable_area_sqm',
                'electricity_unit_price',
                'water_unit_price',
                'default_service_fee_per_sqm',
                'min_rent_price_per_sqm',
                'max_rent_price_per_sqm',
                'grade',
            ]);

        // 1.2 Gửi sai enum grade và max_rent nhỏ hơn min_rent -> Phải chặn 422
        $invalidPayload = [
            'building_code' => 'BLD-TEST-ERR',
            'name' => 'Tòa nhà lỗi',
            'address' => '123 Đường Test',
            'ward' => 'Phường 1',
            'district' => 'Quận 1',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 10,
            'total_leasable_area_sqm' => 500,
            'electricity_unit_price' => 3500,
            'water_unit_price' => 15000,
            'default_service_fee_per_sqm' => 50000,
            'min_rent_price_per_sqm' => 200000,
            'max_rent_price_per_sqm' => 100000, // Nhỏ hơn min_rent
            'grade' => 'INVALID_GRADE_XYZ',     // Không phải A, B, C
        ];

        $resInvalid = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $invalidPayload);

        $resInvalid->assertStatus(422)
            ->assertJsonValidationErrors(['max_rent_price_per_sqm', 'grade']);
    }

    /**
     * 2. Kiểm tra UpdateBuildingRequest: kế thừa đúng rules của Store và cho phép update từng phần.
     */
    public function test_update_building_inherits_store_rules_and_validates_correctly(): void
    {
        // 2.1 Cập nhật vi phạm: total_floors = 0 (phải min:1) và grade sai
        $resInvalid = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/buildings/{$this->building->id}", [
                'total_floors' => 0,
                'grade' => 'X',
            ]);

        $resInvalid->assertStatus(422)
            ->assertJsonValidationErrors(['total_floors', 'grade']);

        // 2.2 Cập nhật hợp lệ: giữ nguyên mã tòa nhà của chính nó (unique ignore self)
        $resValid = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/buildings/{$this->building->id}", [
                'building_code' => $this->building->building_code,
                'name' => 'Tên Tòa Nhà Mới Sau Update',
            ]);

        $resValid->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Tên Tòa Nhà Mới Sau Update');
    }

    /**
     * 3. Kiểm tra Unit Validation (StoreUnitRequest & UpdateUnitRequest).
     */
    public function test_unit_validation_fails_on_invalid_data_and_succeeds_on_valid_update(): void
    {
        // 3.1 Store Unit với payload rỗng -> Phải bắt các trường required
        $resStore = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/units', []);

        $resStore->assertStatus(422)
            ->assertJsonValidationErrors(['building_id', 'unit_code', 'floor_number', 'area_sqm', 'base_rental_price']);

        // 3.2 Update Unit với diện tích âm -> Phải chặn min:1
        $resUpdateFail = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/units/{$this->unit->id}", [
                'area_sqm' => 0,
            ]);

        $resUpdateFail->assertStatus(422)
            ->assertJsonValidationErrors(['area_sqm']);

        // 3.3 Update Unit hợp lệ
        $resUpdateSuccess = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/units/{$this->unit->id}", [
                'name' => 'Phòng Họp VIP',
                'base_rental_price' => 350000,
            ]);

        $resUpdateSuccess->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * 4. Kiểm tra Trait HasStaffRules: bắt lỗi email sai định dạng, số điện thoại không hợp lệ, password yếu.
     */
    public function test_has_staff_rules_validates_email_phone_and_password(): void
    {
        // 4.1 Bắt lỗi phone không đúng chuẩn Việt Nam và password < 8 ký tự trên Kế toán
        $resAcc = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/accountants', [
                'full_name' => 'Kế Toán Test',
                'email' => 'kt.test@officeleasing.vn',
                'phone' => '12345', // Sai định dạng SĐT VN
                'password' => '123', // < 8 ký tự
            ]);

        $resAcc->assertStatus(422)
            ->assertJsonValidationErrors(['phone', 'password']);

        // 4.2 Bắt lỗi trên Sales: email sai định dạng
        $resSale = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/sales', [
                'full_name' => 'Sale Test',
                'email' => 'not-an-email',
                'password' => 'ValidPassword123!',
            ]);

        $resSale->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * 5. Kiểm tra Trait NormalizesArrayInput: hỗ trợ gán ID đơn lẻ và kiểm tra định dạng UUID.
     */
    public function test_normalizes_array_input_handles_singular_and_validates_uuids(): void
    {
        // 5.1 Gửi UUID sai định dạng -> Phải báo lỗi validation
        $resInvalidUuid = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", [
                'sales_ids' => ['not-a-valid-uuid'],
            ]);

        $resInvalidUuid->assertStatus(422)
            ->assertJsonValidationErrors(['sales_ids.0']);

        // 5.2 Gửi 'sales_id' đơn lẻ -> Trait NormalizesArrayInput tự động đóng gói thành mảng 'sales_ids'
        $resSingular = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", [
                'sales_id' => $this->sale->id,
                'is_active' => true,
            ]);

        $resSingular->assertStatus(201)
            ->assertJsonPath('status', 'success');
    }

    /**
     * 6. Kiểm tra StorePortalLeadRequest: quy tắc đặt lịch trước 3 ngày và chuẩn hóa số điện thoại.
     */
    public function test_portal_lead_validation_enforces_rules(): void
    {
        // 6.1 Đặt lịch khảo sát chỉ sau 1 ngày (vi phạm quy định ít nhất 3 ngày)
        $resTour = $this->postJson('/api/v1/portal/leads', [
            'contact_full_name' => 'Khách Thuê Test',
            'phone' => '0912345678',
            'email' => 'khach.test@gmail.com',
            'preferred_site_tour_at' => now()->addDay(1)->format('Y-m-d H:i:s'),
        ]);

        $resTour->assertStatus(422)
            ->assertJsonValidationErrors(['preferred_site_tour_at']);

        // 6.2 Thiếu họ tên và email sai
        $resMissing = $this->postJson('/api/v1/portal/leads', [
            'email' => 'invalid-mail',
        ]);

        $resMissing->assertStatus(422)
            ->assertJsonValidationErrors(['contact_full_name', 'phone', 'email']);
    }

    /**
     * 7. Kiểm tra Ticket Validation (StoreTenantTicketRequest).
     */
    public function test_tenant_ticket_validation(): void
    {
        // 7.1 Gửi category không có trong enum TicketCategory
        $resCat = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->postJson('/api/v1/tenant/tickets', [
                'building_id' => $this->building->id,
                'title' => 'Sự cố mất điện',
                'description' => 'Mô tả chi tiết sự cố',
                'category' => 'INVALID_CATEGORY_ABC',
            ]);

        $resCat->assertStatus(422)
            ->assertJsonValidationErrors(['category']);

        // 7.2 Thiếu các trường bắt buộc
        $resEmpty = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->postJson('/api/v1/tenant/tickets', []);

        $resEmpty->assertStatus(422)
            ->assertJsonValidationErrors(['building_id', 'title', 'description', 'category']);
    }
}
