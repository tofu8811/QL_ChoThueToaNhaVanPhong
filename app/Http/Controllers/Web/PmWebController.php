<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Services\PortalDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller chuyên trách Phân hệ Ban Quản Lý Tòa Nhà (Property Manager Portal - PM).
 * Tự động lọc dữ liệu theo phạm vi các tòa nhà được phân công (Building Scoping).
 */
class PmWebController extends Controller
{
    public function __construct(
        protected PortalDashboardService $portalDashboardService
    ) {}

    /**
     * Bảng điều khiển Vận hành & Kỹ thuật Tòa nhà.
     */
    public function dashboard(Request $request): View
    {
        $viewData = $this->getPmViewData($request, 'dashboard', 'Tổng quan vận hành tòa nhà phụ trách');
        return view('pm.dashboard', $viewData);
    }

    /**
     * Danh mục tài nguyên mặt bằng thuộc quyền quản lý của PM.
     */
    public function buildings(Request $request): View
    {
        $viewData = $this->getPmViewData($request, 'buildings', 'Mặt bằng các tòa nhà phụ trách');
        return view('pm.buildings', $viewData);
    }

    /**
     * Ký duyệt Hợp đồng thuê & Biên bản bàn giao kỹ thuật.
     */
    public function contracts(Request $request): View
    {
        $viewData = $this->getPmViewData($request, 'contracts', 'Ký duyệt hợp đồng & Bàn giao');
        return view('pm.contracts', $viewData);
    }

    /**
     * Tiếp nhận và Xử lý Ticket sự cố, thi công ngoài giờ.
     */
    public function tickets(Request $request): View
    {
        $viewData = $this->getPmViewData($request, 'tickets', 'Tiếp nhận & Xử lý Ticket sự cố');
        return view('pm.tickets', $viewData);
    }

    /**
     * Nhập chỉ số Điện, Nước, Gửi xe định kỳ hàng tháng.
     */
    public function utilities(Request $request): View
    {
        $viewData = $this->getPmViewData($request, 'utilities', 'Chỉ số điện nước & Dịch vụ tòa nhà');
        return view('pm.utilities', $viewData);
    }

    /**
     * Chuẩn bị dữ liệu hiển thị có lọc phạm vi tòa nhà cho PM (Case 1 & Case 2).
     */
    protected function getPmViewData(Request $request, string $activeTab, string $breadcrumb): array
    {
        $user = $request->attributes->get('current_web_user') ?? auth()->user();
        if (!$user) {
            $user = PropertyManager::where('email', 'pm.demo@officeleasing.vn')->first();
        }

        $viewData = $this->portalDashboardService->getDashboardPortalData();

        $selectedBuildingId = $request->query('building_id', 'ALL');
        if (empty($selectedBuildingId)) {
            $selectedBuildingId = 'ALL';
        }

        if ($user instanceof PropertyManager) {
            $allAssignedBuildings = $user->buildings()->get();
        } else {
            $allAssignedBuildings = \App\Models\Building::all();
        }

        $allAssignedBuildingIds = $allAssignedBuildings->pluck('id')->toArray();
        if ($selectedBuildingId !== 'ALL' && in_array($selectedBuildingId, $allAssignedBuildingIds)) {
            $assignedBuildingIds = [$selectedBuildingId];
            $assignedBuildingsCount = 1;
        } else {
            $selectedBuildingId = 'ALL';
            $assignedBuildingIds = $allAssignedBuildingIds;
            $assignedBuildingsCount = count($allAssignedBuildingIds);
        }

        // 1. Lọc danh sách tòa nhà quản trị an toàn (serverBuildings)
        $viewData['serverBuildings'] = $viewData['serverBuildings']->filter(function ($b) use ($assignedBuildingIds) {
            return in_array($b['id'] ?? '', $assignedBuildingIds);
        })->values();

        // 2. Lọc danh sách hợp đồng thuê thuộc các tòa nhà PM quản lý
        $viewData['recentContracts'] = $viewData['recentContracts']->filter(function ($c) use ($assignedBuildingIds) {
            return in_array($c->building_id ?? '', $assignedBuildingIds);
        })->values();

        // 3. Lọc danh sách phiếu sự cố / bảo trì thuộc tòa nhà PM quản lý
        $viewData['recentTickets'] = $viewData['recentTickets']->filter(function ($t) use ($assignedBuildingIds) {
            return in_array($t->building_id ?? '', $assignedBuildingIds);
        })->values();

        // 4. Lọc thông báo cước / công nợ thuộc tòa nhà PM quản lý
        $viewData['recentDebitNotes'] = $viewData['recentDebitNotes']->filter(function ($dn) use ($assignedBuildingIds) {
            return in_array($dn->building_id ?? '', $assignedBuildingIds);
        })->values();

        // 5. Tái tính toán toàn bộ chỉ số KPI vận hành thực tế cho các tòa nhà được phân công
        $totalUnits = \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->count();
        $leasedUnits = \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->where('status', \App\Enums\UnitStatus::LEASED)->count();
        $availableUnits = \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->where('status', \App\Enums\UnitStatus::AVAILABLE)->count();
        $expiringUnits = \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->whereIn('status', [
            \App\Enums\UnitStatus::EXPIRING_SOON,
            \App\Enums\UnitStatus::MAINTENANCE,
            \App\Enums\UnitStatus::RESERVED,
        ])->count();

        $leasedArea = (float) \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->where('status', \App\Enums\UnitStatus::LEASED)->sum('area_sqm');
        $availArea = (float) \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->where('status', \App\Enums\UnitStatus::AVAILABLE)->sum('area_sqm');
        $expArea = (float) \App\Models\Unit::whereIn('building_id', $assignedBuildingIds)->whereIn('status', [
            \App\Enums\UnitStatus::EXPIRING_SOON,
            \App\Enums\UnitStatus::MAINTENANCE,
            \App\Enums\UnitStatus::RESERVED,
        ])->sum('area_sqm');
        $totalArea = $leasedArea + $availArea + $expArea;

        $rentedPct = $totalUnits > 0 ? round(($leasedUnits / $totalUnits) * 100, 1) : 0.0;
        $availPct = $totalUnits > 0 ? round(($availableUnits / $totalUnits) * 100, 1) : 0.0;
        $expPct = $totalUnits > 0 ? round(max(0, 100 - $rentedPct - $availPct), 1) : 0.0;

        $openTicketsCount = \App\Models\Ticket::whereIn('building_id', $assignedBuildingIds)->whereIn('status', [
            \App\Enums\TicketStatus::NEW,
            \App\Enums\TicketStatus::IN_PROGRESS,
            \App\Enums\TicketStatus::ON_HOLD,
        ])->count();

        if ($selectedBuildingId !== 'ALL') {
            $selBld = $allAssignedBuildings->firstWhere('id', $selectedBuildingId);
            $totalLeasableArea = (float) ($selBld?->total_leasable_area_sqm ?? 0);
        } else {
            $totalLeasableArea = (float) $allAssignedBuildings->sum('total_leasable_area_sqm');
        }
        if ($totalLeasableArea <= 0) {
            $totalLeasableArea = $totalArea;
        }

        $viewData['stats']['total_buildings'] = $assignedBuildingsCount;
        $viewData['stats']['total_units'] = $totalUnits;
        $viewData['stats']['rented_units'] = $leasedUnits;
        $viewData['stats']['available_units'] = $availableUnits;
        $viewData['stats']['expiring_units'] = $expiringUnits;
        $viewData['stats']['rented_area'] = $leasedArea;
        $viewData['stats']['available_area'] = $availArea;
        $viewData['stats']['expiring_area'] = $expArea;
        $viewData['stats']['total_units_area'] = $totalArea;
        $viewData['stats']['rented_pct'] = $rentedPct;
        $viewData['stats']['available_pct'] = $availPct;
        $viewData['stats']['expiring_pct'] = $expPct;
        $viewData['stats']['offset_avail'] = -$rentedPct;
        $viewData['stats']['offset_exp'] = -($rentedPct + $availPct);
        $viewData['stats']['open_tickets'] = $openTicketsCount;
        $viewData['stats']['total_leasable_area'] = $totalLeasableArea;

        // Biểu đồ lấp đầy chỉ tính riêng các tòa nhà theo phạm vi lọc
        $viewData['stats']['building_performances'] = \App\Models\Building::whereIn('id', $assignedBuildingIds)
            ->withCount([
                'units as total_u',
                'units as leased_u' => fn ($q) => $q->where('status', \App\Enums\UnitStatus::LEASED),
            ])
            ->get()
            ->map(function ($b) {
                $pct = $b->total_u > 0 ? round(($b->leased_u / $b->total_u) * 100, 1) : 0.0;
                $barColor = $pct >= 80 ? '#10b981' : ($pct >= 40 ? '#2563eb' : '#f59e0b');

                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'district' => $b->district ?? '',
                    'total_units' => $b->total_u,
                    'leased_units' => $b->leased_u,
                    'occupancy_pct' => $pct,
                    'bar_color' => $barColor,
                ];
            })
            ->toArray();

        $viewData['assignedBuildings'] = $allAssignedBuildings;
        $viewData['selectedBuildingId'] = $selectedBuildingId;
        $viewData['selectedBuilding'] = ($selectedBuildingId !== 'ALL') ? $allAssignedBuildings->firstWhere('id', $selectedBuildingId) : null;

        $viewData['currentUserName'] = $user?->full_name ?? $user?->name ?? 'Nguyễn Vận Hành (PM Demo)';
        $viewData['currentUserEmail'] = $user?->email ?? 'pm.demo@officeleasing.vn';
        $viewData['currentUserSub'] = 'Ban Quản Lý • Vận hành & Kỹ thuật';
        $viewData['currentUserRoleLabel'] = 'BAN QUẢN LÝ';
        $viewData['avatarInitial'] = 'PM';
        $viewData['roleBadge'] = 'Ban Quản Lý';
        $viewData['assignedBuildingsCount'] = $assignedBuildingsCount;
        $viewData['activeTab'] = $activeTab;
        $viewData['breadcrumb'] = $breadcrumb;

        return $viewData;
    }
}
