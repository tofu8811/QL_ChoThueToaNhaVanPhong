<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Building;
use App\Models\Province;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module1ApiTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'code' => 79,
            'name' => 'TP Hồ Chí Minh',
            'slug' => 'tp-ho-chi-minh',
            'code_name' => 'thanh_pho_ho_chi_minh',
        ]);
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
     * Test tra cứu danh mục tỉnh/thành phố.
     */
    public function test_can_get_provinces(): void
    {
        $response = $this->getJson('/api/v1/provinces');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test danh sách tòa nhà trên Public Portal.
     */
    public function test_can_list_public_buildings(): void
    {
        $response = $this->getJson('/api/v1/portal/buildings');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * Test danh sách mặt bằng trên Public Portal.
     */
    public function test_can_list_public_units(): void
    {
        $response = $this->getJson('/api/v1/portal/units');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * Test toàn bộ vòng đời CRUD của Tòa nhà và Mặt bằng qua API.
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

        $createBuildingRes = $this->postJson('/api/v1/admin/buildings', $buildingPayload);
        $createBuildingRes->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $buildingId = $createBuildingRes->json('data.id');
        $slug = $createBuildingRes->json('data.slug');

        // 2. GET PUBLIC DETAIL BY SLUG
        $publicDetailRes = $this->getJson("/api/v1/portal/buildings/{$slug}");
        $publicDetailRes->assertStatus(200)
            ->assertJsonPath('data.id', $buildingId);

        // 3. UPDATE BUILDING
        $updateBuildingRes = $this->putJson("/api/v1/admin/buildings/{$buildingId}", [
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

        $createUnitRes = $this->postJson('/api/v1/admin/units', $unitPayload);
        $createUnitRes->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $unitId = $createUnitRes->json('data.id');

        // 5. GET PUBLIC UNIT DETAIL
        $publicUnitRes = $this->getJson("/api/v1/portal/units/{$unitId}");
        $publicUnitRes->assertStatus(200)
            ->assertJsonPath('data.unit_code', 'NEX-1201');

        // 6. UPDATE UNIT
        $updateUnitRes = $this->putJson("/api/v1/admin/units/{$unitId}", [
            'base_rental_price' => 980000,
        ]);
        $updateUnitRes->assertStatus(200)
            ->assertJsonPath('data.base_rental_price', '980000.00');

        // 7. CHANGE UNIT STATUS TO MAINTENANCE
        $statusRes = $this->patchJson("/api/v1/admin/units/{$unitId}/status", [
            'status' => 'MAINTENANCE',
        ]);
        $statusRes->assertStatus(200)
            ->assertJsonPath('data.status', 'MAINTENANCE');

        // 8. DELETE UNIT
        $deleteUnitRes = $this->deleteJson("/api/v1/admin/units/{$unitId}");
        $deleteUnitRes->assertStatus(200);

        // 9. DELETE BUILDING
        $deleteBuildingRes = $this->deleteJson("/api/v1/admin/buildings/{$buildingId}");
        $deleteBuildingRes->assertStatus(200);
    }

    /**
     * Test validation lỗi khi thiếu trường bắt buộc.
     */
    public function test_building_validation_fails_when_missing_required_fields(): void
    {
        $response = $this->postJson('/api/v1/admin/buildings', []);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }
}
