<?php

namespace Tests\Feature;

use App\Enums\UnitStatus;
use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Province;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature Test kiểm tra toàn diện chức năng phân trang (Pagination)
 * cho API Tài nguyên Tòa nhà & Mặt bằng sàn trong phân hệ Admin và Ban Quản Lý (PM).
 */
class BuildingAndUnitPaginationApiTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Admin $admin;
    protected string $adminToken;
    protected PropertyManager $pm;
    protected string $pmToken;

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
            'employee_code' => 'ADM-PAG-01',
            'full_name' => 'Nguyễn Quản Trị Phân Trang',
            'email' => 'admin.pag@officeleasing.vn',
            'phone' => '0912345678',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);
        $this->adminToken = $this->admin->createToken('admin-token', ['admin'])->plainTextToken;

        $this->pm = PropertyManager::create([
            'employee_code' => 'PM-PAG-01',
            'full_name' => 'Trần Ban Quản Lý Phân Trang',
            'email' => 'pm.pag@officeleasing.vn',
            'phone' => '0912345679',
            'password' => 'secret123',
            'role' => 'PROPERTY_MANAGER',
            'status' => 'ACTIVE',
        ]);
        $this->pmToken = $this->pm->createToken('pm-token', ['pm'])->plainTextToken;
    }

    /**
     * Test 1: Phân trang danh sách tòa nhà Admin (GET /api/v1/admin/buildings?page=1&per_page=10).
     */
    public function test_admin_buildings_pagination_returns_correct_pages_and_meta(): void
    {
        // Tạo 15 tòa nhà
        for ($i = 1; $i <= 15; $i++) {
            Building::create([
                'province_code' => 79,
                'province' => 'TP Hồ Chí Minh',
                'building_code' => sprintf('BLD-2026-%04d', $i),
                'name' => "Tòa Nhà Văn Phòng {$i}",
                'slug' => "toa-nha-van-phong-{$i}",
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'address' => "Số {$i} Đường Lê Duẩn",
                'grade' => ($i % 2 === 0) ? 'A' : 'B',
                'total_floors' => 20,
                'basements' => 2,
                'total_leasable_area_sqm' => 5000,
                'min_rent_price_per_sqm' => 350000,
                'is_published' => true,
            ]);
        }

        // Trang 1: per_page=10
        $responsePage1 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/v1/admin/buildings?page=1&per_page=10');

        $responsePage1->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.has_more', true);

        $dataPage1 = $responsePage1->json('data');
        $this->assertCount(10, $dataPage1);

        // Trang 2: per_page=10
        $responsePage2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/v1/admin/buildings?page=2&per_page=10');

        $responsePage2->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.has_more', false);

        $dataPage2 = $responsePage2->json('data');
        $this->assertCount(5, $dataPage2);

        // Đảm bảo không trùng lặp giữa 2 trang
        $idsPage1 = collect($dataPage1)->pluck('id');
        $idsPage2 = collect($dataPage2)->pluck('id');
        $this->assertEmpty($idsPage1->intersect($idsPage2));
    }

    /**
     * Test 2: Phân trang danh sách tòa nhà theo bộ lọc tìm kiếm và phân hạng.
     */
    public function test_admin_buildings_pagination_with_filters(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Building::create([
                'province_code' => 79,
                'province' => 'TP Hồ Chí Minh',
                'building_code' => sprintf('BLD-FLT-%04d', $i),
                'name' => ($i <= 7) ? "Landmark Tower {$i}" : "Bitexco Office {$i}",
                'slug' => "building-flt-{$i}",
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'address' => "Địa chỉ {$i}",
                'grade' => ($i <= 7) ? 'A' : 'B',
                'total_floors' => 20,
                'basements' => 2,
                'total_leasable_area_sqm' => 5000,
                'min_rent_price_per_sqm' => 400000,
                'is_published' => true,
            ]);
        }

        // Lọc theo search="Landmark" và per_page=5 -> có 7 bản ghi -> 2 trang
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/v1/admin/buildings?search=Landmark&page=1&per_page=5');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 7);
        $this->assertCount(5, $response->json('data'));

        // Trang 2 của kết quả tìm kiếm
        $responseP2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/v1/admin/buildings?search=Landmark&page=2&per_page=5');

        $responseP2->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 7);
        $this->assertCount(2, $responseP2->json('data'));
    }

    /**
     * Test 3: Phân trang danh sách sàn mặt bằng trong modal (GET /api/v1/admin/units?building_id=...&per_page=5).
     */
    public function test_building_units_pagination_returns_correct_pages_and_meta(): void
    {
        $building = Building::create([
            'province_code' => 79,
            'province' => 'TP Hồ Chí Minh',
            'building_code' => 'BLD-UNIT-01',
            'name' => 'Tòa Nhà Thử Nghiệm Sàn',
            'slug' => 'toa-nha-thu-nghiem-san',
            'district' => 'Bình Thạnh',
            'ward' => 'Phường 25',
            'address' => 'Số 100 Ung Văn Khiêm',
            'grade' => 'B',
            'total_floors' => 15,
            'basements' => 2,
            'total_leasable_area_sqm' => 6000,
            'min_rent_price_per_sqm' => 300000,
            'is_published' => true,
        ]);

        // Tạo 12 sàn mặt bằng
        for ($i = 1; $i <= 12; $i++) {
            Unit::create([
                'building_id' => $building->id,
                'name' => "Mặt bằng {$i}",
                'unit_code' => sprintf('L%02d-01', $i),
                'floor_number' => $i,
                'area_sqm' => 150.0 + $i * 10,
                'base_rental_price' => 300000,
                'service_fee' => 50000,
                'status' => UnitStatus::AVAILABLE,
                'handover_condition' => 'BASIC',
            ]);
        }

        // Trang 1: 5 sàn
        $resUnitP1 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/v1/admin/units?building_id={$building->id}&page=1&per_page=5");

        $resUnitP1->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 12);
        $this->assertCount(5, $resUnitP1->json('data'));

        // Trang 2: 5 sàn
        $resUnitP2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/v1/admin/units?building_id={$building->id}&page=2&per_page=5");

        $resUnitP2->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.total', 12);
        $this->assertCount(5, $resUnitP2->json('data'));

        // Trang 3: 2 sàn còn lại
        $resUnitP3 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson("/api/v1/admin/units?building_id={$building->id}&page=3&per_page=5");

        $resUnitP3->assertOk()
            ->assertJsonPath('meta.current_page', 3)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.has_more', false);
        $this->assertCount(2, $resUnitP3->json('data'));
    }

    /**
     * Test 4: Phân trang Ban Quản Lý (PM) chỉ hiển thị các tòa nhà thuộc phạm vi phân công.
     */
    public function test_pm_buildings_pagination_respects_assigned_scope(): void
    {
        // Tạo 8 tòa nhà, PM chỉ được phân công 5 tòa
        $assignedBuildings = [];
        for ($i = 1; $i <= 8; $i++) {
            $b = Building::create([
                'province_code' => 79,
                'province' => 'TP Hồ Chí Minh',
                'building_code' => sprintf('BLD-PM-%04d', $i),
                'name' => "Cao ốc PM {$i}",
                'slug' => "cao-oc-pm-{$i}",
                'district' => 'Quận 3',
                'ward' => 'Phường Võ Thị Sáu',
                'address' => "Số {$i} Nam Kỳ Khởi Nghĩa",
                'grade' => 'A',
                'total_floors' => 18,
                'basements' => 2,
                'total_leasable_area_sqm' => 4500,
                'min_rent_price_per_sqm' => 450000,
                'is_published' => true,
            ]);

            if ($i <= 5) {
                $assignedBuildings[] = $b->id;
                $this->pm->buildings()->attach($b->id, [
                    'assigned_at' => now(),
                ]);
            }
        }

        // PM yêu cầu danh sách tòa nhà với per_page=3 -> tổng 5 tòa phân công -> 2 trang
        $resPmP1 = $this->withHeader('Authorization', 'Bearer ' . $this->pmToken)
            ->getJson('/api/v1/admin/buildings?page=1&per_page=3');

        $resPmP1->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 5);
        $this->assertCount(3, $resPmP1->json('data'));

        // Kiểm tra tất cả tòa nhà trả về đều nằm trong danh sách phân công
        foreach ($resPmP1->json('data') as $item) {
            $this->assertContains($item['id'], $assignedBuildings);
        }

        // Trang 2 của PM: 2 tòa còn lại
        $resPmP2 = $this->withHeader('Authorization', 'Bearer ' . $this->pmToken)
            ->getJson('/api/v1/admin/buildings?page=2&per_page=3');

        $resPmP2->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 5);
        $this->assertCount(2, $resPmP2->json('data'));
    }
}
