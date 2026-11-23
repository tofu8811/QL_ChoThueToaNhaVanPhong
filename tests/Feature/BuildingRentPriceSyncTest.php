<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\Province;
use App\Models\Unit;
use App\Services\BuildingService;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingRentPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Province $province;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'code' => 79,
            'name' => 'Thành phố Hồ Chí Minh',
            'slug' => 'thanh-pho-ho-chi-minh',
            'division_type' => 'thành phố trung ương',
            'codename' => 'ho_chi_minh',
            'phone_code' => 28,
        ]);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-SYNC-01',
            'full_name' => 'Admin Sync Test',
            'email' => 'admin.sync@officeleasing.vn',
            'phone' => '0988776655',
            'password' => 'secret123',
            'role' => 'admin',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * 1. Kiểm tra tạo mới tòa nhà mà không cần truyền giá thuê (min_rent_price_per_sqm).
     * Mặc định giá thuê sẽ là 0.00.
     */
    public function test_can_create_building_without_rent_price(): void
    {
        $token = $this->admin->createToken('admin-token', ['admin'])->plainTextToken;

        $payload = [
            'name' => 'Tòa Nhà Test Auto Price',
            'province_code' => 79,
            'district' => 'Quận 1',
            'address' => '123 Lê Lợi',
            'grade' => 'A',
            'total_floors' => 20,
            'total_leasable_area_sqm' => 15000,
            'default_service_fee_per_sqm' => 120000,
            'electricity_fee_desc' => 'Theo đồng hồ riêng',
            'water_fee_desc' => 'Miễn phí theo chỉ số',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/admin/buildings', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.min_rent_price_per_sqm', '0.00')
            ->assertJsonPath('data.max_rent_price_per_sqm', '0.00');

        $this->assertDatabaseHas('buildings', [
            'name' => 'Tòa Nhà Test Auto Price',
            'min_rent_price_per_sqm' => 0.00,
            'max_rent_price_per_sqm' => 0.00,
        ]);
    }

    /**
     * 2. Kiểm tra khi thêm mặt bằng (unit) đầu tiên, min và max price tự động cập nhật theo giá sàn đó.
     */
    public function test_building_price_range_syncs_on_first_unit(): void
    {
        $buildingService = app(BuildingService::class);
        $building = $buildingService->storeBuilding([
            'name' => 'Tòa Nhà Landmark ABC',
            'province_code' => 79,
            'province' => 'Thành phố Hồ Chí Minh',
            'district' => 'Bình Thạnh',
            'ward' => 'Phường 22',
            'address' => '720A Điện Biên Phủ',
            'grade' => 'A',
            'total_floors' => 30,
            'total_leasable_area_sqm' => 25000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 25000,
            'default_service_fee_per_sqm' => 150000,
            'min_rent_price_per_sqm' => 0,
            'max_rent_price_per_sqm' => 0,
        ], $this->admin->id);

        $this->assertEquals(0, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(0, (float) $building->max_rent_price_per_sqm);

        // Thêm Unit 1 với giá 650.000 đ/m2
        $unitService = app(UnitService::class);
        $unit1 = $unitService->storeUnit([
            'building_id' => $building->id,
            'unit_code' => 'U-101',
            'floor_number' => 10,
            'area_sqm' => 200,
            'base_rental_price' => 650000,
        ]);

        $building->refresh();
        $this->assertEquals(650000, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(650000, (float) $building->max_rent_price_per_sqm);

        // Thêm Unit 2 với giá 850.000 đ/m2
        $unit2 = $unitService->storeUnit([
            'building_id' => $building->id,
            'unit_code' => 'U-102',
            'floor_number' => 15,
            'area_sqm' => 350,
            'base_rental_price' => 850000,
        ]);

        $building->refresh();
        $this->assertEquals(650000, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(850000, (float) $building->max_rent_price_per_sqm);

        // Thêm Unit 3 với giá thấp nhất 500.000 đ/m2
        $unit3 = $unitService->storeUnit([
            'building_id' => $building->id,
            'unit_code' => 'U-103',
            'floor_number' => 5,
            'area_sqm' => 150,
            'base_rental_price' => 500000,
        ]);

        $building->refresh();
        $this->assertEquals(500000, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(850000, (float) $building->max_rent_price_per_sqm);

        // Sửa Unit 3 nâng giá lên 600.000 đ/m2
        $unitService->updateUnit($unit3, [
            'base_rental_price' => 600000,
        ]);

        $building->refresh();
        $this->assertEquals(600000, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(850000, (float) $building->max_rent_price_per_sqm);

        // Xóa Unit 3 -> Khoảng giá phải tự động co lại thành [650.000, 850.000]
        $unitService->deleteUnit($unit3);

        $building->refresh();
        $this->assertEquals(650000, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(850000, (float) $building->max_rent_price_per_sqm);

        // Xóa tiếp Unit 1 và Unit 2 -> Khi không còn unit nào, giá về 0.00
        $unitService->deleteUnit($unit1);
        $unitService->deleteUnit($unit2);

        $building->refresh();
        $this->assertEquals(0, (float) $building->min_rent_price_per_sqm);
        $this->assertEquals(0, (float) $building->max_rent_price_per_sqm);
    }
}
