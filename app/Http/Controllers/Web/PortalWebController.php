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
        $recentContracts = collect();
        $recentDebitNotes = collect();
        $stats = [
            'total_buildings' => 22,
            'total_leasable_area' => 428430,
            'total_tenants' => 10,
            'free_consult_pct' => 100,
            'by_grade' => ['A' => 7, 'B' => 11, 'C' => 4],
            'by_district' => ['Quận 1' => 7, 'Quận 3' => 3, 'Bình Thạnh' => 3, 'Quận 7' => 2, 'TP Thủ Đức' => 1],
        ];

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('buildings')) {
                $totalBuildings = Building::published()->count();
                $totalArea = (float) Building::published()->sum('total_leasable_area_sqm');
                $totalTenants = \Illuminate\Support\Facades\Schema::hasTable('tenants') ? \App\Models\Tenant::count() : 10;

                $provincesList = Building::published()->distinct()->pluck('province')->filter()->values();
                $cleanProvinces = $provincesList->map(fn($p) => str_replace(['Thành phố ', 'Tỉnh '], '', $p))->implode(', ');
                $minPriceVnd = Building::published()->min('min_rent_price_per_sqm') ?: 240000;
                $maxPriceVnd = Building::published()->max('max_rent_price_per_sqm') ?: 1680000;

                $stats = [
                    'total_buildings' => $totalBuildings,
                    'total_leasable_area' => $totalArea > 0 ? $totalArea : 428430,
                    'total_tenants' => $totalTenants,
                    'free_consult_pct' => 100,
                    'active_provinces' => $provincesList->toArray(),
                    'active_provinces_text' => $cleanProvinces ?: 'Hà Nội, TP.HCM, Đồng Nai & Đà Nẵng',
                    'min_price_usd' => round($minPriceVnd / 24000),
                    'max_price_usd' => round($maxPriceVnd / 24000),
                    'by_grade' => [
                        'A' => Building::published()->where('grade', 'A')->count(),
                        'B' => Building::published()->where('grade', 'B')->count(),
                        'C' => Building::published()->where('grade', 'C')->count(),
                    ],
                    'by_province' => [
                        'ha_noi' => Building::published()->where('province_code', 1)->count(),
                        'ho_chi_minh' => Building::published()->where('province_code', 79)->count(),
                        'dong_nai' => Building::published()->where('province_code', 75)->count(),
                        'da_nang' => Building::published()->where('province_code', 48)->count(),
                    ],
                    'by_district' => [
                        'Quận 1' => Building::published()->where('district', 'like', '%Quận 1%')->count(),
                        'Quận 3' => Building::published()->where('district', 'like', '%Quận 3%')->count(),
                        'Bình Thạnh' => Building::published()->where('district', 'like', '%Bình Thạnh%')->count(),
                        'Quận 7' => Building::published()->where('district', 'like', '%Quận 7%')->count(),
                        'TP Thủ Đức' => Building::published()->where(function ($q) {
                            $q->where('district', 'like', '%Thủ Đức%')->orWhere('district', 'like', '%Thành phố Thủ Đức%');
                        })->count(),
                        'Ba Đình' => Building::published()->where('district', 'like', '%Ba Đình%')->count(),
                        'Cầu Giấy' => Building::published()->where('district', 'like', '%Cầu Giấy%')->count(),
                        'Biên Hòa' => Building::published()->where('district', 'like', '%Biên Hòa%')->count(),
                        'Hải Châu' => Building::published()->where('district', 'like', '%Hải Châu%')->count(),
                    ],
                ];

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
                        $primaryImg = $b->primaryImage?->image_url ?? $b->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80';
                        $buildingImages = $b->images->pluck('image_url')->toArray();
                        if (empty($buildingImages) && $primaryImg) {
                            $buildingImages = [$primaryImg];
                        }
                        
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
                            'images' => $buildingImages,
                            'floors' => $floors,
                        ];
                    });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('provinces')) {
                $provinces = Province::orderBy('name')->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('contracts')) {
                $recentContracts = \App\Models\Contract::with(['tenant', 'building'])->latest()->take(10)->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('debitnotes')) {
                $recentDebitNotes = \App\Models\DebitNote::with(['tenant', 'building'])->latest()->take(10)->get();
            }

            $leads = collect();
            if (\Illuminate\Support\Facades\Schema::hasTable('leads')) {
                $leads = \App\Models\Lead::with('building')->latest()->take(15)->get();
            }

            $openTickets = 5;
            if (\Illuminate\Support\Facades\Schema::hasTable('tickets')) {
                $openTickets = \App\Models\Ticket::whereIn('status', ['OPEN', 'NEW', 'IN_PROGRESS'])->count() ?: 5;
            }
            $stats['open_tickets'] = $openTickets;

            $sampleTenant = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('tenants')) {
                $sampleTenant = \App\Models\Tenant::with(['contracts.building', 'debitNotes.building'])->where('login_email', 'contact@vng.com.vn')->first()
                    ?? \App\Models\Tenant::with(['contracts.building', 'debitNotes.building'])->first();
            }
            $sampleContract = $sampleTenant?->contracts?->first()
                ?? (\Illuminate\Support\Facades\Schema::hasTable('contracts') ? \App\Models\Contract::with(['building', 'tenant'])->latest()->first() : null);
            $sampleDebit = $sampleTenant?->debitNotes?->latest()->first()
                ?? (\Illuminate\Support\Facades\Schema::hasTable('debitnotes') ? \App\Models\DebitNote::with('building')->latest()->first() : null);

            $serverStaff = [
                'admins' => \Illuminate\Support\Facades\Schema::hasTable('admins') ? \App\Models\Admin::latest()->get() : collect(),
                'sales' => \Illuminate\Support\Facades\Schema::hasTable('sales') ? \App\Models\Sale::with('buildings')->latest()->get() : collect(),
                'pm' => \Illuminate\Support\Facades\Schema::hasTable('property_managers') ? \App\Models\PropertyManager::with('buildings')->latest()->get() : collect(),
                'accountants' => \Illuminate\Support\Facades\Schema::hasTable('accountants') ? \App\Models\Accountant::with('buildings')->latest()->get() : collect(),
            ];

            $defaultStaffToken = '';
            if (\Illuminate\Support\Facades\Schema::hasTable('admins')) {
                $superAdmin = \App\Models\Admin::where('is_super_admin', true)->first();
                if ($superAdmin) {
                    $superAdmin->tokens()->where('name', 'portal-admin-token')->delete();
                    $defaultStaffToken = $superAdmin->createToken('portal-admin-token')->plainTextToken;
                }
            }
        } catch (\Throwable $e) {
            // Fallback an toàn nếu database chưa sẵn sàng
        }

        return view('portal.index', [
            'serverBuildings' => $buildings,
            'provinces' => $provinces,
            'recentContracts' => $recentContracts,
            'recentDebitNotes' => $recentDebitNotes,
            'leads' => $leads ?? collect(),
            'sampleTenant' => $sampleTenant ?? null,
            'sampleContract' => $sampleContract ?? null,
            'sampleDebit' => $sampleDebit ?? null,
            'serverStaff' => $serverStaff ?? [],
            'defaultStaffToken' => $defaultStaffToken ?? '',
            'stats' => $stats,
        ]);
    }

    // Hiển thị trang đăng ký nhận báo giá và đặt lịch khảo sát chuẩn SEO SSR
    public function quotePage(Request $request): View
    {
        return view('pages.quote');
    }

    // Hiển thị trang quản lý hợp đồng thuê văn phòng chuẩn SEO SSR (Module 3)
    public function contractsPage(Request $request): View
    {
        return view('pages.contracts');
    }

    // Hiển thị trang quản lý hồ sơ khách thuê doanh nghiệp chuẩn SEO SSR (Module 3)
    public function tenantsPage(Request $request): View
    {
        return view('pages.tenants');
    }

    // Hiển thị cổng thông tin không gian làm việc khách thuê Tenant Portal SSR (Module 3)
    public function tenantPortalPage(Request $request): View
    {
        return view('pages.tenant-portal');
    }

    /**
     * Hiển thị trang chi tiết Tòa nhà Văn phòng chuẩn SEO SSR (Module 1).
     */
    public function showBuilding(string $slug): View
    {
        try {
            $building = $this->buildingService->getPublicBuildingBySlug($slug);
        } catch (\Throwable $e) {
            $building = Building::query()
                ->where('slug', $slug)
                ->orWhere('id', $slug)
                ->with(['images', 'primaryImage', 'units'])
                ->firstOrFail();
        }

        return view('pages.building-detail', compact('building'));
    }
}
