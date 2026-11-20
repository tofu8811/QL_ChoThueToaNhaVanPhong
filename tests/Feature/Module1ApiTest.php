<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Module1ApiTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Admin $admin;
    protected string $adminToken;
    protected Sale $sale;
    protected string $saleToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'code' => 79,
            'name' => 'TP Hồ Chí Minh',
            'slug' => 'tp-ho-chi-minh',
            'code_name' => 'thanh_pho_ho_chi_minh',
        ]);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-M1-01',
            'full_name' => 'Nguyễn Quản Trị Module 1',
            'email' => 'admin.m1@officeleasing.vn',
            'phone' => '0912345671',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);

        $this->adminToken = $this->admin->createToken('admin-m1', ['admin'])->plainTextToken;

        $this->sale = Sale::create([
            'employee_code' => 'SAL-M1-01',
            'full_name' => 'Lê Kinh Doanh Module 1',
            'email' => 'sale.m1@officeleasing.vn',
            'phone' => '0912345672',
            'password' => 'secret123',
            'role' => 'SALES',
            'status' => 'ACTIVE',
        ]);

        $this->saleToken = $this->sale->createToken('sale-m1', ['sales'])->plainTextToken;
    }

    /**
     * Test Swagger UI & OpenAPI JSON endpoints.
     */
    public function test_swagger_documentation_is_accessible(): void
    {
        $response = $this->get('/api/documentation');
        $response->assertStatus(200);

        $jsonResponse = $this->get('/docs');
        $jsonResponse->assertStatus(200)
            ->assertJsonPath('openapi', '3.0.0')
            ->assertJsonPath('info.title', 'Hệ Thống Quản Lý Và Cho Thuê Tòa Nhà Văn Phòng API');
    }

    /**
     * Test tra cứu danh mục tỉnh/thành phố (Public).
     */
    public function test_can_get_provinces(): void
    {
        $response = $this->getJson('/api/v1/provinces');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');

        // Test chi tiết tỉnh thành theo code (STT 2)
        $detailRes = $this->getJson('/api/v1/provinces/79');
        $detailRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.province.code', 79);
    }

    /**
     * Test danh sách tòa nhà trên Public Portal (Public).
     */
    public function test_can_list_public_buildings(): void
    {
        $response = $this->getJson('/api/v1/portal/buildings');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * Test danh sách mặt bằng trên Public Portal (Public).
     */
    public function test_can_list_public_units(): void
    {
        $response = $this->getJson('/api/v1/portal/units');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * Test chặn truy cập quản trị tòa nhà khi chưa xác thực (401 Unauthorized).
     */
    public function test_unauthenticated_user_cannot_access_admin_buildings(): void
    {
        $response = $this->getJson('/api/v1/admin/buildings');
        $response->assertStatus(401);

        $postResponse = $this->postJson('/api/v1/admin/buildings', []);
        $postResponse->assertStatus(401);
    }

    /**
     * Test chặn vai trò không hợp lệ thao tác tòa nhà (403 Forbidden).
     */
    public function test_unauthorized_role_cannot_manage_buildings(): void
    {
        // Sales không có quyền tạo tòa nhà (chỉ Admin)
        $response = $this->withHeader('Authorization', "Bearer {$this->saleToken}")
            ->postJson('/api/v1/admin/buildings', [
                'name' => 'Tòa Nhà Bị Cấm',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test toàn bộ vòng đời CRUD của Tòa nhà và Mặt bằng qua API với quyền Admin.
     */
    public function test_full_crud_lifecycle_for_buildings_and_units(): void
    {
        // 1. CREATE BUILDING
        $buildingPayload = [
            'building_code' => 'NEXUS-TEST-' . time(),
            'name' => 'The Nexus Tower ' . time(),
            'address' => '3A Tôn Đức Thắng',
            'ward' => 'Bến Nghé',
            'district' => 'Quận 1',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => $this->province->code,
            'total_floors' => 35,
            'total_leasable_area_sqm' => 45000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 25000,
            'default_service_fee_per_sqm' => 150000,
            'min_rent_price_per_sqm' => 850000,
            'max_rent_price_per_sqm' => 1200000,
            'grade' => 'A',
            'is_published' => true,
        ];

        $createBuildingRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $buildingPayload);
        $createBuildingRes->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $buildingId = $createBuildingRes->json('data.id');
        $slug = $createBuildingRes->json('data.slug');

        // 1b. GET ADMIN BUILDINGS LIST (STT 5)
        $adminBuildingsRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/buildings');
        $adminBuildingsRes->assertStatus(200);

        // 1c. GET ADMIN BUILDING DETAIL (STT 7)
        $adminDetailRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson("/api/v1/admin/buildings/{$buildingId}");
        $adminDetailRes->assertStatus(200)
            ->assertJsonPath('data.id', $buildingId);

        // 1d. UPLOAD BUILDING IMAGES (STT 10)
        Storage::fake('public');
        $uploadImgRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$buildingId}/images", [
                'images' => [
                    UploadedFile::fake()->image('building_front.jpg'),
                    UploadedFile::fake()->image('building_lobby.jpg'),
                ],
                'caption' => 'Mặt tiền tòa nhà',
            ]);
        $uploadImgRes->assertStatus(201);
        $imageId1 = $uploadImgRes->json('data.0.id');
        $imageId2 = $uploadImgRes->json('data.1.id');

        // 1e. SET PRIMARY BUILDING IMAGE (STT 12)
        if ($imageId2) {
            $primaryRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
                ->patchJson("/api/v1/admin/buildings/{$buildingId}/images/{$imageId2}/set-primary");
            $primaryRes->assertStatus(200);
        }

        // 1f. DELETE BUILDING IMAGE (STT 11)
        if ($imageId1) {
            $delImgRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
                ->deleteJson("/api/v1/admin/buildings/{$buildingId}/images/{$imageId1}");
            $delImgRes->assertStatus(200);
        }

        // 2. GET PUBLIC DETAIL BY SLUG (Public Portal)
        $publicDetailRes = $this->getJson("/api/v1/portal/buildings/{$slug}");
        $publicDetailRes->assertStatus(200)
            ->assertJsonPath('data.id', $buildingId);

        // 3. UPDATE BUILDING
        $updateBuildingRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/buildings/{$buildingId}", [
                'total_floors' => 36,
                'name' => 'The Nexus Tower Updated',
            ]);
        $updateBuildingRes->assertStatus(200)
            ->assertJsonPath('data.total_floors', 36);

        // 4. CREATE UNIT
        $unitPayload = [
            'building_id' => $buildingId,
            'unit_code' => 'NEX-1201',
            'name' => 'Văn phòng Cao cấp Tầng 12',
            'floor_number' => 12,
            'area_sqm' => 320.5,
            'base_rental_price' => 950000,
            'status' => 'AVAILABLE',
        ];

        $createUnitRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/units', $unitPayload);
        $createUnitRes->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $unitId = $createUnitRes->json('data.id');

        // 4b. GET ADMIN UNITS LIST (STT 15)
        $adminUnitsRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/units');
        $adminUnitsRes->assertStatus(200);

        // 4c. GET ADMIN UNIT DETAIL (STT 17)
        $adminUnitDetailRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson("/api/v1/admin/units/{$unitId}");
        $adminUnitDetailRes->assertStatus(200)
            ->assertJsonPath('data.id', $unitId);

        // 4d. UPLOAD UNIT IMAGES (STT 21)
        $uploadUnitImgRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/units/{$unitId}/images", [
                'images' => [
                    UploadedFile::fake()->image('floorplan_12.png'),
                ],
                'caption' => 'Sơ đồ mặt bằng sàn tầng 12',
            ]);
        $uploadUnitImgRes->assertStatus(201);
        $unitImageId = $uploadUnitImgRes->json('data.0.id');

        // 4e. DELETE UNIT IMAGE (STT 22)
        if ($unitImageId) {
            $delUnitImgRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
                ->deleteJson("/api/v1/admin/units/{$unitId}/images/{$unitImageId}");
            $delUnitImgRes->assertStatus(200);
        }

        // 5. GET PUBLIC UNIT DETAIL (Public Portal)
        $publicUnitRes = $this->getJson("/api/v1/portal/units/{$unitId}");
        $publicUnitRes->assertStatus(200)
            ->assertJsonPath('data.unit_code', 'NEX-1201');

        // 6. UPDATE UNIT
        $updateUnitRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/units/{$unitId}", [
                'base_rental_price' => 980000,
            ]);
        $updateUnitRes->assertStatus(200)
            ->assertJsonPath('data.base_rental_price', '980000.00');

        // 7. CHANGE UNIT STATUS TO MAINTENANCE
        $statusRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->patchJson("/api/v1/admin/units/{$unitId}/status", [
                'status' => 'MAINTENANCE',
            ]);
        $statusRes->assertStatus(200)
            ->assertJsonPath('data.status', 'MAINTENANCE');

        // 8. DELETE UNIT
        $deleteUnitRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/units/{$unitId}");
        $deleteUnitRes->assertStatus(200);

        // 9. DELETE BUILDING
        $deleteBuildingRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$buildingId}");
        $deleteBuildingRes->assertStatus(200);
    }

    /**
     * Test validation lỗi khi thiếu trường bắt buộc.
     */
    public function test_building_validation_fails_when_missing_required_fields(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', []);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }
}
