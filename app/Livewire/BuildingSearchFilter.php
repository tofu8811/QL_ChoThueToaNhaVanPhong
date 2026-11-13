<?php

namespace App\Livewire;

use App\Models\Building;
use App\Services\BuildingService;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire Component: Tìm kiếm & Lọc Tòa Nhà Văn Phòng Reactive (Module 1).
 */
class BuildingSearchFilter extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $keyword = '';

    #[Url(history: true)]
    public string $province = 'all';

    #[Url(history: true)]
    public string $district = 'all';

    #[Url(history: true)]
    public string $grade = 'all';

    #[Url(history: true)]
    public string $price = 'all';

    #[Url(history: true)]
    public string $area = 'all';

    #[Url(history: true)]
    public string $sortBy = 'featured';

    public int $perPage = 8;

    public function mount(): void
    {
        if (request()->filled('province')) {
            $this->province = (string) request()->query('province');
        } elseif (request()->filled('province_code')) {
            $this->province = (string) request()->query('province_code');
        }
        if (request()->filled('district')) {
            $this->district = (string) request()->query('district');
        }
        if (request()->filled('grade')) {
            $this->grade = (string) request()->query('grade');
        }
        if (request()->filled('keyword')) {
            $this->keyword = (string) request()->query('keyword');
        }
        if (request()->filled('price')) {
            $this->price = (string) request()->query('price');
        }
        if (request()->filled('area')) {
            $this->area = (string) request()->query('area');
        }
    }

    /**
     * Tùy chỉnh view phân trang theo phong cách Maison Office.
     */
    public function paginationView(): string
    {
        return 'livewire.pagination';
    }

    /**
     * Tự động reset trang về 1 khi bất kỳ tiêu chí tìm kiếm nào thay đổi.
     */
    public function updated($property): void
    {
        if (in_array($property, ['keyword', 'province', 'district', 'grade', 'price', 'area', 'sortBy', 'perPage'])) {
            if ($property === 'province') {
                $this->district = 'all';
            }
            $this->resetPage();
        }
    }

    #[On('filter-province')]
    public function setProvince(string $province): void
    {
        $this->province = $province;
        $this->district = 'all';
        $this->resetPage();
    }

    #[On('filter-district')]
    public function setDistrict(string $district): void
    {
        $this->district = $district;
        $this->resetPage();
    }

    #[On('filter-grade')]
    public function setGrade(string $grade): void
    {
        $this->grade = $grade;
        $this->resetPage();
    }

    #[On('reset-filters')]
    public function resetFilters(): void
    {
        $this->reset(['keyword', 'province', 'district', 'grade', 'price', 'area', 'sortBy']);
        $this->resetPage();
    }

    public function render(BuildingService $buildingService)
    {
        // 1. Chuyển đổi bộ lọc giá ($/m2 -> VND/m2)
        $minPrice = null;
        $maxPrice = null;
        if ($this->price === 'under20') {
            $maxPrice = 20 * 24000;
        } elseif ($this->price === '20to35') {
            $minPrice = 20 * 24000;
            $maxPrice = 35 * 24000;
        } elseif ($this->price === 'over35') {
            $minPrice = 35 * 24000;
        }

        // 2. Chuyển đổi bộ lọc diện tích (m2)
        $minArea = null;
        $maxArea = null;
        if ($this->area === 'under150') {
            $maxArea = 150;
        } elseif ($this->area === '150to500') {
            $minArea = 150;
            $maxArea = 500;
        } elseif ($this->area === 'over500') {
            $minArea = 500;
        }

        // 3. Chuyển đổi sắp xếp
        $sortCol = 'created_at';
        $sortOrder = 'desc';
        if ($this->sortBy === 'price-asc') {
            $sortCol = 'min_rent_price_per_sqm';
            $sortOrder = 'asc';
        } elseif ($this->sortBy === 'price-desc') {
            $sortCol = 'min_rent_price_per_sqm';
            $sortOrder = 'desc';
        } elseif ($this->sortBy === 'area-desc') {
            $sortCol = 'total_leasable_area_sqm';
            $sortOrder = 'desc';
        }

        $filterParams = [
            'keyword'       => $this->keyword,
            'province'      => $this->province !== 'all' ? $this->province : null,
            'province_code' => is_numeric($this->province) ? (int) $this->province : null,
            'district'      => $this->district !== 'all' ? $this->district : null,
            'grade'         => $this->grade !== 'all' ? $this->grade : null,
            'min_price'     => $minPrice,
            'max_price'     => $maxPrice,
            'min_area'      => $minArea,
            'max_area'      => $maxArea,
            'sort_by'       => $sortCol,
            'sort_order'    => $sortOrder,
        ];

        $buildings = $buildingService->getPublicBuildings($filterParams, $this->perPage);

        // 4. Thống kê phụ trợ (Market stats & Sidebar counts)
        $totalArea = (float) Building::published()->sum('total_leasable_area_sqm');
        $totalBuildings = Building::published()->count();
        $totalTenants = \Illuminate\Support\Facades\Schema::hasTable('tenants') ? \App\Models\Tenant::count() : 10;
        $totalProvinces = Building::published()->distinct()->count('province_code');

        $stats = [
            'total_buildings' => $totalBuildings > 0 ? $totalBuildings : 39,
            'total_area' => $totalArea > 0 ? $totalArea : 1050000,
            'total_tenants' => $totalTenants > 0 ? $totalTenants : 10,
            'total_provinces' => $totalProvinces > 0 ? $totalProvinces : 4,
            'count_grade_a' => Building::published()->where('grade', 'A')->count(),
            'count_grade_b' => Building::published()->where('grade', 'B')->count(),
            'count_grade_c' => Building::published()->where('grade', 'C')->count(),
            // Thống kê theo Tỉnh / Thành phố
            'count_hanoi'   => Building::published()->where('province_code', 1)->count(),
            'count_hcm'     => Building::published()->where('province_code', 79)->count(),
            'count_dongnai' => Building::published()->where('province_code', 75)->count(),
            'count_danang'  => Building::published()->where('province_code', 48)->count(),
            // Thống kê theo Quận / Khu vực trọng điểm
            'count_q1'      => Building::published()->where('district', 'like', '%Quận 1%')->count(),
            'count_q3'      => Building::published()->where('district', 'like', '%Quận 3%')->count(),
            'count_bt'      => Building::published()->where('district', 'like', '%Bình Thạnh%')->count(),
            'count_q7'      => Building::published()->where('district', 'like', '%Quận 7%')->count(),
            'count_td'      => Building::published()->where('district', 'like', '%Thủ Đức%')->count(),
            'count_badinh'  => Building::published()->where('district', 'like', '%Ba Đình%')->count(),
            'count_caugiay' => Building::published()->where('district', 'like', '%Cầu Giấy%')->count(),
            'count_hoankiem'=> Building::published()->where('district', 'like', '%Hoàn Kiếm%')->count(),
            'count_bienhoa' => Building::published()->where('district', 'like', '%Biên Hòa%')->count(),
            'count_haichau' => Building::published()->where('district', 'like', '%Hải Châu%')->count(),
        ];

        return view('livewire.building-search-filter', [
            'buildings' => $buildings,
            'stats'     => $stats,
            'keyword'   => $this->keyword,
            'province'  => $this->province,
            'district'  => $this->district,
            'grade'     => $this->grade,
            'price'     => $this->price,
            'area'      => $this->area,
            'sortBy'    => $this->sortBy,
        ]);
    }
}
