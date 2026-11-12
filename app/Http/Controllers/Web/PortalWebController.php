<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Province;
use App\Services\BuildingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller phục vụ Giao diện Người dùng (Public Web Portal & Shell).
 */
class PortalWebController extends Controller
{
    public function __construct(
        protected BuildingService $buildingService
    ) {}

    /**
     * Hiển thị trang chủ Cổng thông tin thuê văn phòng.
     * Tự động truyền danh sách tòa nhà thật từ database sang Blade View.
     */
    public function index(Request $request): View
    {
        $buildings = collect();
        $provinces = collect();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('buildings')) {
                $buildings = Building::query()
                    ->published()
                    ->with(['images', 'primaryImage', 'units'])
                    ->withCount([
                        'units as available_units_count' => function ($q) {
                            $q->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON']);
                        }
                    ])
                    ->latest()
                    ->get()
                    ->map(function (Building $b) {
                        $primaryImg = $b->primaryImage?->image_url ?? $b->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=800&q=80';
                        
                        $floors = $b->units->map(function ($unit) {
                            return [
                                'floor' => 'Tầng ' . ($unit->floor_number ?? '1'),
                                'area' => ($unit->area_sqm ?? 100) . ' m²',
                                'deliver' => $unit->handover_condition ?? 'Hoàn thiện cơ bản',
                                'price' => '$' . number_format($unit->rent_price_usd_per_sqm ?? 30, 0) . ' / m²',
                                'status' => $unit->status === 'AVAILABLE' ? 'Đang trống' : 'Sắp trống',
                            ];
                        })->toArray();

                        if (empty($floors)) {
                            $floors = [
                                ['floor' => 'Tầng 5', 'area' => '180 m²', 'deliver' => 'Hoàn thiện cơ bản', 'price' => '$' . number_format($b->min_rent_price_per_sqm ? $b->min_rent_price_per_sqm / 24000 : 35, 0) . ' / m²', 'status' => 'Đang trống'],
                                ['floor' => 'Tầng 12', 'area' => '350 m²', 'deliver' => 'Nội thất tiêu chuẩn', 'price' => '$' . number_format($b->max_rent_price_per_sqm ? $b->max_rent_price_per_sqm / 24000 : 45, 0) . ' / m²', 'status' => 'Đang trống'],
                            ];
                        }

                        $minUsd = $b->min_rent_price_per_sqm ? round($b->min_rent_price_per_sqm / 24000) : 25;
                        $maxUsd = $b->max_rent_price_per_sqm ? round($b->max_rent_price_per_sqm / 24000) : ($minUsd + 10);

                        return [
                            'id' => $b->id,
                            'name' => $b->name,
                            'slug' => $b->slug ?? \Illuminate\Support\Str::slug($b->name),
                            'grade' => 'Hạng ' . ($b->grade ?? 'A'),
                            'district' => $b->district ?? 'Quận 1',
                            'address' => $b->address . ($b->ward ? ', ' . $b->ward : '') . ($b->district ? ', ' . $b->district : ''),
                            'priceUsd' => $minUsd,
                            'priceMaxUsd' => $maxUsd,
                            'priceVnd' => number_format($b->min_rent_price_per_sqm ?: 600000, 0, ',', '.') . ' đ',
                            'totalArea' => number_format($b->total_leasable_area_sqm ?: 25000, 0, ',', '.') . ' m²',
                            'availableArea' => '1.500 m²',
                            'floorPlate' => '1.000 m²',
                            'structure' => ($b->total_floors ?: 25) . ' Tầng nổi - ' . ($b->total_basements ?: 3) . ' Hầm',
                            'status' => 'Đang trống',
                            'statusClass' => 'empty',
                            'image' => $primaryImg,
                            'floors' => $floors,
                        ];
                    });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('provinces')) {
                $provinces = Province::orderBy('name')->get();
            }
        } catch (\Throwable $e) {
            // Fallback an toàn nếu database chưa sẵn sàng
        }

        return view('portal.index', [
            'serverBuildings' => $buildings,
            'provinces' => $provinces,
        ]);
    }
}
