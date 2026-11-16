<?php

namespace App\Providers;

use App\Models\Building;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share nationwide regional hubs data with portal views
        View::composer([
            'portal.components.header',
            'portal.partials.header',
            'portal.components.footer',
            'portal.partials.footer',
            'portal.layouts.master',
            'portal.index',
            'livewire.building-search-filter'
        ], function ($view) {
            if (!Schema::hasTable('buildings')) {
                return;
            }

            try {
                $headerLocations = Cache::remember('portal_header_locations_v4', 60, function () {
                    // Truy vấn động 100% từ Database cho từng Tỉnh / Thành phố
                    $buildProvinceData = function (int $provinceCode, string $name) {
                        $bQuery = Building::published()->where('province_code', $provinceCode);
                        $count = (clone $bQuery)->count();

                        // Danh sách Quận / Huyện có tòa nhà thực tế trong DB
                        $districts = (clone $bQuery)
                            ->select('district', DB::raw('count(*) as count'))
                            ->whereNotNull('district')
                            ->where('district', '!=', '')
                            ->groupBy('district')
                            ->orderByDesc('count')
                            ->get()
                            ->map(fn ($d) => ['name' => $d->district, 'count' => (int) $d->count])
                            ->values()
                            ->all();

                        // Danh sách Phường / Xã có tòa nhà thực tế trong DB kèm quận/huyện
                        $wards = (clone $bQuery)
                            ->select('ward', 'district', DB::raw('count(*) as count'))
                            ->whereNotNull('ward')
                            ->where('ward', '!=', '')
                            ->groupBy('ward', 'district')
                            ->orderByDesc('count')
                            ->get()
                            ->map(function ($w) {
                                $wName = trim($w->ward);
                                $dName = trim($w->district);
                                $displayLabel = $wName . ($dName ? " ($dName)" : "");
                                return [
                                    'ward' => $wName,
                                    'district' => $dName,
                                    'display_label' => $displayLabel,
                                    'count' => (int) $w->count,
                                ];
                            })
                            ->values()
                            ->all();

                        $totalWards = count($wards);
                        $perCol = max(1, (int) ceil($totalWards / 3));
                        $col1 = array_slice($wards, 0, $perCol);
                        $col2 = array_slice($wards, $perCol, $perCol);
                        $col3 = array_slice($wards, $perCol * 2);

                        $geo = [];
                        try {
                            $geo = app(\App\Services\LocationService::class)->getProvinceGeoData($provinceCode);
                        } catch (\Throwable $e) {}

                        return [
                            'name' => $name,
                            'code' => $provinceCode,
                            'count' => $count,
                            'districts' => $districts,
                            'wards' => $wards,
                            'col1' => $col1,
                            'col2' => $col2,
                            'col3' => $col3,
                            'geo' => $geo,
                        ];
                    };

                    $hanoi = $buildProvinceData(1, 'Hà Nội');
                    $hcm = $buildProvinceData(79, 'TP. Hồ Chí Minh');
                    $danang = $buildProvinceData(48, 'Đà Nẵng');

                    // Các khu vực khác (Đồng Nai, Bình Dương, Long An, Hải Phòng...)
                    $otherProvinces = Building::published()
                        ->whereNotIn('province_code', [1, 79, 48])
                        ->select('province', 'province_code', DB::raw('count(*) as count'))
                        ->whereNotNull('province')
                        ->where('province', '!=', '')
                        ->groupBy('province', 'province_code')
                        ->orderByDesc('count')
                        ->get()
                        ->map(fn ($p) => [
                            'province' => $p->province,
                            'province_code' => $p->province_code,
                            'count' => (int) $p->count,
                        ])
                        ->values()
                        ->all();

                    $totalNationwide = Building::published()->count();

                    return [
                        'total_nationwide' => $totalNationwide,
                        'hanoi' => $hanoi,
                        'hcm' => $hcm,
                        'danang' => $danang,
                        'other_provinces' => $otherProvinces,
                    ];
                });

                $view->with('headerLocations', $headerLocations);
            } catch (\Throwable $e) {
                // Graceful fallback if database is not reachable during setup
            }
        });
    }
}
