<?php

namespace Tests\Feature;

use App\Livewire\BuildingSearchFilter;
use App\Livewire\FloorVacancyList;
use App\Models\Building;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Module1LivewireComponentTest extends TestCase
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
     * Kiểm tra BuildingSearchFilter render thành công.
     */
    public function test_building_search_filter_component_renders_successfully(): void
    {
        Livewire::test(BuildingSearchFilter::class)
            ->assertStatus(200)
            ->assertSee('Cho Thuê Văn Phòng')
            ->assertSee('Tất cả Quận / Huyện')
            ->assertSee('Hạng văn phòng');
    }

    /**
     * Kiểm tra tương tác lọc reactive của BuildingSearchFilter.
     */
    public function test_building_search_filter_can_filter_by_district_and_keyword(): void
    {
        Livewire::test(BuildingSearchFilter::class)
            ->set('keyword', 'Vietcombank')
            ->assertSet('keyword', 'Vietcombank')
            ->call('setDistrict', 'Quận 1')
            ->assertSet('district', 'Quận 1')
            ->call('setGrade', 'A')
            ->assertSet('grade', 'A')
            ->call('resetFilters')
            ->assertSet('keyword', '')
            ->assertSet('district', 'all')
            ->assertSet('grade', 'all');
    }

    /**
     * Kiểm tra phân trang tùy biến Maison Office và đổi số lượng hiển thị.
     */
    public function test_building_search_filter_pagination_and_pagesize_work(): void
    {
        Livewire::test(BuildingSearchFilter::class)
            ->set('perPage', 4)
            ->assertSee('mo-pagination-wrapper')
            ->assertSee('mo-page-btn')
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->call('previousPage')
            ->assertSet('paginators.page', 1);
    }

    /**
     * Kiểm tra FloorVacancyList component render thành công.
     */
    public function test_floor_vacancy_list_component_renders_successfully(): void
    {
        $building = Building::published()->first();
        $this->assertNotNull($building);

        Livewire::test(FloorVacancyList::class, ['buildingId' => (string) $building->id])
            ->assertStatus(200)
            ->assertSee('Danh Sách Mặt Bằng', false)
            ->assertSee('Vị trí Tầng', false)
            ->assertSee('Diện tích trống', false);
    }

    /**
     * Kiểm tra route chi tiết tòa nhà SEO /toa-nha/{slug} hiển thị đúng.
     */
    public function test_building_detail_page_renders_with_seo_and_livewire(): void
    {
        $building = Building::published()->first();
        $this->assertNotNull($building);

        $response = $this->get(route('buildings.show', $building->slug ?? $building->id));
        $response->assertStatus(200);
        $response->assertSee($building->name);
        $response->assertSee('Vị trí Tầng', false);
    }
}
