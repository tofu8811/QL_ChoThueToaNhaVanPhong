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
                $headerLocations = Cache::remember('portal_header_locations_v3', 120, function () {
                    // Hà Nội (province_code: 1)
                    $hnQuery = Building::published()->where('province_code', 1);
                    $hnCount = (clone $hnQuery)->count();
                    $hnDistricts = (clone $hnQuery)
                        ->select('district', DB::raw('count(*) as count'))
                        ->whereNotNull('district')
                        ->where('district', '!=', '')
                        ->groupBy('district')
                        ->orderByDesc('count')
                        ->get()
                        ->map(fn ($d) => ['district' => $d->district, 'count' => (int) $d->count])
                        ->values()
                        ->all();

                    // TP. Hồ Chí Minh (province_code: 79)
                    $hcmQuery = Building::published()->where('province_code', 79);
                    $hcmCount = (clone $hcmQuery)->count();
                    $hcmDistricts = (clone $hcmQuery)
                        ->select('district', DB::raw('count(*) as count'))
                        ->whereNotNull('district')
                        ->where('district', '!=', '')
                        ->groupBy('district')
                        ->orderByDesc('count')
                        ->get()
                        ->map(fn ($d) => ['district' => $d->district, 'count' => (int) $d->count])
                        ->values()
                        ->all();

                    // Đà Nẵng (province_code: 48)
                    $dnQuery = Building::published()->where('province_code', 48);
                    $dnCount = (clone $dnQuery)->count();
                    $dnDistricts = (clone $dnQuery)
                        ->select('district', DB::raw('count(*) as count'))
                        ->whereNotNull('district')
                        ->where('district', '!=', '')
                        ->groupBy('district')
                        ->orderByDesc('count')
                        ->get()
                        ->map(fn ($d) => ['district' => $d->district, 'count' => (int) $d->count])
                        ->values()
                        ->all();

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
                        'hanoi' => [
                            'name' => 'Hà Nội',
                            'code' => 1,
                            'count' => $hnCount,
                            'districts' => $hnDistricts,
                        ],
                        'hcm' => [
                            'name' => 'TP. Hồ Chí Minh',
                            'code' => 79,
                            'count' => $hcmCount,
                            'districts' => $hcmDistricts,
                        ],
                        'danang' => [
                            'name' => 'Đà Nẵng',
                            'code' => 48,
                            'count' => $dnCount,
                            'districts' => $dnDistricts,
                        ],
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
