<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuildingImageRequiredTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $adminToken;
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

        $this->admin = Admin::create([
            'employee_code' => 'ADM-IMG-01',
            'full_name' => 'Nguyễn Quản Trị Test Ảnh',
            'email' => 'admin_test_img@system.com',
            'phone' => '0912345679',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);

        $this->adminToken = $this->admin->createToken('admin-img', ['admin'])->plainTextToken;
    }

    /**
     * Kiểm thử tạo mới tòa nhà đính kèm danh sách hình ảnh (Multipart FormData).
     */
    public function test_can_create_building_with_images_directly(): void
    {
        Storage::fake('public');

        $payload = [
            'name' => 'Tòa Nhà Test Hình Ảnh',
            'address' => '123 Đường Nguyễn Huệ',
            'district' => 'Quận 1',
            'ward' => 'Phường Bến Nghé',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 12,
            'total_leasable_area_sqm' => 5000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 25000,
            'default_service_fee_per_sqm' => 100000,
            'grade' => 'A',
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('lobby.png'),
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->post('/api/v1/admin/buildings', $payload);

        $response->assertStatus(201);
        $buildingId = $response->json('data.id');
        $this->assertNotNull($buildingId);

        $building = Building::with('images')->find($buildingId);
        $this->assertCount(2, $building->images);
        $this->assertTrue((bool) $building->images->first()->is_primary);
    }

    /**
     * Kiểm thử chọn ảnh bất kỳ làm ảnh chính thông qua primary_image_index.
     */
    public function test_can_create_building_with_custom_primary_image_index(): void
    {
        Storage::fake('public');

        $payload = [
            'name' => 'Tòa Nhà Test Ảnh Thứ 3 Làm Chính',
            'address' => '789 Đường Hai Bà Trưng',
            'district' => 'Quận 1',
            'ward' => 'Phường Đa Kao',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 15,
            'total_leasable_area_sqm' => 6000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 25000,
            'default_service_fee_per_sqm' => 100000,
            'grade' => 'A',
            'primary_image_index' => 2,
            'images' => [
                UploadedFile::fake()->image('img0.jpg'),
                UploadedFile::fake()->image('img1.png'),
                UploadedFile::fake()->image('img2_primary.jpg'),
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->post('/api/v1/admin/buildings', $payload);

        $response->assertStatus(201);
        $buildingId = $response->json('data.id');
        $this->assertNotNull($buildingId);

        $building = Building::with('images')->find($buildingId);
        $this->assertCount(3, $building->images);

        $images = $building->images->sortBy('display_order')->values();
        $this->assertFalse((bool) $images[0]->is_primary);
        $this->assertFalse((bool) $images[1]->is_primary);
        $this->assertTrue((bool) $images[2]->is_primary);
    }

    /**
     * Kiểm thử không thể xóa hình ảnh cuối cùng của tòa nhà (Ràng buộc Bắt buộc có ảnh).
     */
    public function test_cannot_delete_last_image_of_building(): void
    {
        Storage::fake('public');

        $building = Building::create([
            'name' => 'Tòa Nhà Một Ảnh',
            'slug' => 'toa-nha-mot-anh',
            'building_code' => 'BLD-2026-9999',
            'address' => '456 Lê Lợi',
            'district' => 'Quận 1',
            'ward' => 'Phường Bến Nghé',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => 79,
            'total_floors' => 10,
            'total_leasable_area_sqm' => 4000,
            'electricity_unit_price' => 4500,
            'water_unit_price' => 25000,
            'default_service_fee_per_sqm' => 90000,
            'grade' => 'B',
        ]);

        $image = $building->images()->create([
            'image_url' => 'https://storage.supabase.co/test/photo1.jpg',
            'caption' => 'Ảnh duy nhất',
            'is_primary' => true,
            'display_order' => 1,
        ]);

        // Cố gắng xóa ảnh duy nhất
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$building->id}/images/{$image->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Tòa nhà phải có ít nhất 1 hình ảnh đại diện, không thể xóa hình ảnh cuối cùng.');

        $this->assertDatabaseHas('building_images', ['id' => $image->id]);
    }
}
