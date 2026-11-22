<?php

namespace Tests\Feature;

use App\Models\Province;
use App\Services\LocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProvinceDistrictsResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Province::create([
            'code' => 24,
            'name' => 'Thành phố Bắc Ninh',
            'slug' => 'thanh-pho-bac-ninh',
            'division_type' => 'thành phố trung ương',
            'codename' => 'bac_ninh',
            'phone_code' => 222,
        ]);

        Province::create([
            'code' => 79,
            'name' => 'Thành phố Hồ Chí Minh',
            'slug' => 'thanh-pho-ho-chi-minh',
            'division_type' => 'thành phố trung ương',
            'codename' => 'ho_chi_minh',
            'phone_code' => 28,
        ]);
    }

    /**
     * Kiểm tra API GET /api/v1/provinces/{code} trả về đầy đủ districts và geo_details.
     */
    public function test_api_provinces_show_returns_districts(): void
    {
        $response = $this->getJson('/api/v1/provinces/24');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.province.code', 24)
            ->assertJsonPath('data.province.name', 'Thành phố Bắc Ninh')
            ->assertJsonStructure([
                'data' => [
                    'province',
                    'geo_details',
                    'districts',
                ],
            ]);

        $districts = $response->json('data.districts');
        $this->assertIsArray($districts);
        $this->assertNotEmpty($districts);
        // Kiểm tra chứa các quận/huyện chính thức của Bắc Ninh
        $this->assertContains('Thành phố Bắc Ninh', $districts);
        $this->assertContains('Thành phố Từ Sơn', $districts);
        $this->assertContains('Huyện Yên Phong', $districts);

        // Đảm bảo TUYỆT ĐỐI không chứa dummy fallback
        $this->assertNotContains('Quận trung tâm', $districts);
        $this->assertNotContains('Huyện ngoại thành', $districts);
    }

    /**
     * Kiểm tra file cache geo được tạo và lưu trữ trên disk local.
     */
    public function test_geo_json_file_is_stored_on_disk(): void
    {
        $locationService = app(LocationService::class);
        $data = $locationService->getProvinceGeoData(24);

        $this->assertNotEmpty($data['areas']);
        $this->assertTrue(Storage::disk('local')->exists("geo/province_24.json"));

        $cachedContent = json_decode(Storage::disk('local')->get("geo/province_24.json"), true);
        $this->assertEquals('Thành phố Bắc Ninh', $cachedContent['province_name']);
        $this->assertContains('Thành phố Bắc Ninh', $cachedContent['areas']);
    }
}
