<?php

namespace Tests\Feature;

use App\Models\Building;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class PortalWebTest
 *
 * Kiểm thử render trang chủ Portal tích hợp Blade & API.
 */
class PortalWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
    }

    /**
     * Kiểm tra tải trang chủ Public Portal thành công.
     */
    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Cho Thuê Văn Phòng TP.HCM');
        $response->assertSee('Filament Admin');
    }
}
