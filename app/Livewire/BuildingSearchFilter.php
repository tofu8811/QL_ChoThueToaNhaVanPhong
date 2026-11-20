<?php

namespace App\Livewire;

use App\Models\Building;
use App\Services\BuildingService;
use App\Services\LocationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    // Tiêu chí người dùng đang chọn trên Form (chưa submit)
    public string $keyword = '';
    public string $province = 'all';
    public string $district = 'all';
    public string $grade = 'all';
    public string $price = 'all';
    public string $area = 'all';

    // Tiêu chí ĐÃ ÁP DỤNG vào câu truy vấn tìm kiếm (Đồng bộ URL)
    #[Url(history: true, as: 'keyword')]
    public string $appliedKeyword = '';

    #[Url(history: true, as: 'province')]
    public string $appliedProvince = 'all';

    #[Url(history: true, as: 'district')]
    public string $appliedDistrict = 'all';

    #[Url(history: true, as: 'grade')]
    public string $appliedGrade = 'all';

    #[Url(history: true, as: 'price')]
    public string $appliedPrice = 'all';

    #[Url(history: true, as: 'area')]
    public string $appliedArea = 'all';

    #[Url(history: true)]
    public string $sortBy = 'featured';

    public int $perPage = 8;

    public function mount(): void
    {
        $p = request()->query('province') ?: request()->query('province_code');
        if ($p) {
            $this->province = (string) $p;
            $this->appliedProvince = (string) $p;
        }
        if (request()->filled('district')) {
            $this->district = (string) request()->query('district');
            $this->appliedDistrict = $this->district;
        }
        if (request()->filled('grade')) {
            $this->grade = (string) request()->query('grade');
            $this->appliedGrade = $this->grade;
        }
        if (request()->filled('keyword')) {
            $this->keyword = (string) request()->query('keyword');
            $this->appliedKeyword = $this->keyword;
        }
        if (request()->filled('price')) {
            $this->price = (string) request()->query('price');
            $this->appliedPrice = $this->price;
        }
        if (request()->filled('area')) {
            $this->area = (string) request()->query('area');
            $this->appliedArea = $this->area;
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
     * Tự động reset trang khi thay đổi cách sắp xếp hoặc số lượng hiển thị.
     * Khi thay đổi tỉnh thành trên form, đồng bộ danh sách quận/huyện tương ứng.
     */
    public function updated($property): void
    {
        if ($property === 'province') {
            $this->district = 'all';
        }
        if (in_array($property, ['sortBy', 'perPage'])) {
            $this->resetPage();
        }
    }

    /**
     * Thực hiện tìm kiếm theo các tiêu chí đã chọn trên Form và cuộn xuống danh sách kết quả.
     */
    public function applySearch(): void
    {
        $this->appliedKeyword = trim($this->keyword);
        $this->appliedProvince = $this->province;
        $this->appliedDistrict = $this->district;
        $this->appliedGrade = $this->grade;
        $this->appliedPrice = $this->price;
        $this->appliedArea = $this->area;

        $this->resetPage();
        $this->dispatch('scroll-to-listings');
    }

    #[On('filter-province')]
    public function setProvince(string $province): void
    {
        $this->province = $province;
        $this->district = 'all';
        $this->appliedProvince = $province;
        $this->appliedDistrict = 'all';
        $this->resetPage();
        $this->dispatch('scroll-to-listings');
    }

    #[On('filter-district')]
    public function setDistrict(string $district): void
    {
        $this->district = $district;
        $this->appliedDistrict = $district;
        $this->resetPage();
        $this->dispatch('scroll-to-listings');
    }

    #[On('filter-grade')]
    public function setGrade(string $grade): void
    {
        $this->grade = $grade;
        $this->appliedGrade = $grade;
        $this->resetPage();
        $this->dispatch('scroll-to-listings');
    }

    #[On('filter-price')]
    public function setPrice(string $price): void
    {
        $this->price = $price;
        $this->appliedPrice = $price;
        $this->resetPage();
        $this->dispatch('scroll-to-listings');
    }

    #[On('reset-filters')]
    public function resetFilters(): void
    {
        $this->keyword = '';
        $this->province = 'all';
        $this->district = 'all';
        $this->grade = 'all';
        $this->price = 'all';
        $this->area = 'all';

        $this->appliedKeyword = '';
        $this->appliedProvince = 'all';
        $this->appliedDistrict = 'all';
        $this->appliedGrade = 'all';
        $this->appliedPrice = 'all';
        $this->appliedArea = 'all';

        $this->resetPage();
    }

    public function removeFilter(string $type): void
    {
        if ($type === 'keyword') {
            $this->keyword = '';
            $this->appliedKeyword = '';
        } elseif ($type === 'province') {
            $this->province = 'all';
            $this->district = 'all';
            $this->appliedProvince = 'all';
            $this->appliedDistrict = 'all';
        } elseif ($type === 'district') {
            $this->district = 'all';
            $this->appliedDistrict = 'all';
        } elseif ($type === 'grade') {
            $this->grade = 'all';
            $this->appliedGrade = 'all';
        } elseif ($type === 'price') {
            $this->price = 'all';
            $this->appliedPrice = 'all';
        } elseif ($type === 'area') {
            $this->area = 'all';
            $this->appliedArea = 'all';
        }
        $this->resetPage();
    }

    public function render(BuildingService $buildingService, LocationService $locationService)
    {
        // 1. Chuyển đổi bộ lọc giá theo chuẩn VNĐ (VND/m2)
        $minPrice = null;
        $maxPrice = null;
        if ($this->appliedPrice === 'under500k' || $this->appliedPrice === 'under20') {
            $maxPrice = 500000;
        } elseif ($this->appliedPrice === '500kto1m' || $this->appliedPrice === '20to35') {
            $minPrice = 500000;
            $maxPrice = 1000000;
        } elseif ($this->appliedPrice === 'over1m' || $this->appliedPrice === 'over35') {
            $minPrice = 1000000;
        }

        // 2. Chuyển đổi bộ lọc diện tích (m2)
        $minArea = null;
        $maxArea = null;
        if ($this->appliedArea === 'under150') {
            $maxArea = 150;
        } elseif ($this->appliedArea === '150to500') {
            $minArea = 150;
            $maxArea = 500;
        } elseif ($this->appliedArea === 'over500') {
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
            'keyword'       => $this->appliedKeyword,
            'province'      => $this->appliedProvince !== 'all' ? $this->appliedProvince : null,
            'province_code' => is_numeric($this->appliedProvince) ? (int) $this->appliedProvince : null,
            'district'      => $this->appliedDistrict !== 'all' ? $this->appliedDistrict : null,
            'grade'         => $this->appliedGrade !== 'all' ? $this->appliedGrade : null,
            'min_price'     => $minPrice,
            'max_price'     => $maxPrice,
            'min_area'      => $minArea,
            'max_area'      => $maxArea,
            'sort_by'       => $sortCol,
            'sort_order'    => $sortOrder,
        ];

        $buildings = $buildingService->getPublicBuildings($filterParams, $this->perPage);

        // 4. Thống kê phụ trợ (Market stats & Sidebar counts) - Caching 1 giờ để tối ưu 0ms khi lọc
        $stats = Cache::remember('portal_market_stats_summary', 3600, function () {
            $totalArea = (float) Building::published()->sum('total_leasable_area_sqm');
            $totalBuildings = Building::published()->count();
            $totalTenants = \Illuminate\Support\Facades\Schema::hasTable('tenants') ? \App\Models\Tenant::count() : 10;
            $totalProvinces = Building::published()->distinct()->count('province_code');

            return [
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
        });

        // 5. Truy vấn ĐỘNG cho các Quận/Huyện và Phường/Xã có tòa nhà (Cache theo từng Tỉnh để load tức thì)
        $pKey = $this->province ?: 'all';
        $dbDistricts = Cache::remember("portal_geo_districts_{$pKey}", 3600, function () {
            if (is_numeric($this->province)) {
                $pCode = (int) $this->province;
                return Building::published()
                    ->where('province_code', $pCode)
                    ->select('district', DB::raw('count(*) as count'))
                    ->whereNotNull('district')
                    ->where('district', '!=', '')
                    ->groupBy('district')
                    ->orderByDesc('count')
                    ->get()
                    ->map(fn ($d) => ['name' => $d->district, 'count' => (int) $d->count])
                    ->all();
            } else {
                return Building::published()
                    ->select('district', 'province', DB::raw('count(*) as count'))
                    ->whereNotNull('district')
                    ->where('district', '!=', '')
                    ->groupBy('district', 'province')
                    ->orderByDesc('count')
                    ->get()
                    ->map(fn ($d) => ['name' => $d->district, 'province' => $d->province, 'count' => (int) $d->count])
                    ->all();
            }
        });

        $dbWards = Cache::remember("portal_geo_wards_{$pKey}", 3600, function () {
            if (is_numeric($this->province)) {
                $pCode = (int) $this->province;
                return Building::published()
                    ->where('province_code', $pCode)
                    ->select('ward', 'district', DB::raw('count(*) as count'))
                    ->whereNotNull('ward')
                    ->where('ward', '!=', '')
                    ->groupBy('ward', 'district')
                    ->orderByDesc('count')
                    ->get()
                    ->map(function ($w) {
                        $wName = trim($w->ward);
                        $dName = trim($w->district);
                        return [
                            'ward' => $wName,
                            'district' => $dName,
                            'display_label' => $wName . ($dName ? " ($dName)" : ""),
                            'count' => (int) $w->count,
                        ];
                    })
                    ->all();
            } else {
                return Building::published()
                    ->select('ward', 'district', DB::raw('count(*) as count'))
                    ->whereNotNull('ward')
                    ->where('ward', '!=', '')
                    ->groupBy('ward', 'district')
                    ->orderByDesc('count')
                    ->get()
                    ->map(function ($w) {
                        $wName = trim($w->ward);
                        $dName = trim($w->district);
                        return [
                            'ward' => $wName,
                            'district' => $dName,
                            'display_label' => $wName . ($dName ? " ($dName)" : ""),
                            'count' => (int) $w->count,
                        ];
                    })
                    ->all();
            }
        });

        // 6. Đọc động dữ liệu địa giới (Phường mới sáp nhập & Khu vực) từ file storage/app/private/geo/province_{code}.json
        $geoData = [];
        $targetProvinceCode = is_numeric($this->province) ? (int) $this->province : ($this->province === 'all' ? 79 : null);
        if ($targetProvinceCode) {
            $geoData = $locationService->getProvinceGeoData($targetProvinceCode);
        }

        return view('livewire.building-search-filter', [
            'buildings'       => $buildings,
            'stats'           => $stats,
            'geoData'         => $geoData,
            'dbDistricts'     => $dbDistricts,
            'dbWards'         => $dbWards,
            'keyword'         => $this->keyword,
            'province'        => $this->province,
            'district'        => $this->district,
            'grade'           => $this->grade,
            'price'           => $this->price,
            'area'            => $this->area,
            'appliedKeyword'  => $this->appliedKeyword,
            'appliedProvince' => $this->appliedProvince,
            'appliedDistrict' => $this->appliedDistrict,
            'appliedGrade'    => $this->appliedGrade,
            'appliedPrice'    => $this->appliedPrice,
            'appliedArea'     => $this->appliedArea,
            'sortBy'          => $this->sortBy,
        ]);
    }
}
