<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Contract;
use App\Models\Sale;
use App\Services\PortalDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller chuyên trách Phân hệ Kinh doanh & CRM (Sales Portal).
 * Tự động lọc Leads và Hiệu suất cá nhân của Chuyên viên Sales.
 */
class SaleWebController extends Controller
{
    public function __construct(
        protected PortalDashboardService $portalDashboardService
    ) {}

    /**
     * Báo cáo hiệu suất kinh doanh cá nhân của Sales.
     */
    public function dashboard(Request $request): View
    {
        $viewData = $this->getSalesViewData($request, 'dashboard', 'Báo cáo hiệu suất kinh doanh cá nhân');
        return view('sales.dashboard', $viewData);
    }

    /**
     * Quản lý Phễu khách hàng tiềm năng CRM (Kanban Leads Pipeline).
     */
    public function leads(Request $request): View
    {
        $viewData = $this->getSalesViewData($request, 'leads', 'Quản lý Leads Khách hàng & Pipeline CRM');
        return view('sales.leads', $viewData);
    }

    /**
     * Lập báo giá & Thư ngỏ thuê văn phòng (LOI).
     */
    public function quotes(Request $request): View
    {
        $viewData = $this->getSalesViewData($request, 'quotes', 'Lập báo giá & Thư ngỏ thuê (LOI)');
        return view('sales.quotes', $viewData);
    }

    /**
     * Danh mục các mặt bằng còn trống sẵn sàng chào thuê.
     */
    public function availableUnits(Request $request): View
    {
        $viewData = $this->getSalesViewData($request, 'available-units', 'Danh mục mặt bằng trống chào thuê');
        return view('sales.available-units', $viewData);
    }

    /**
     * Quản lý hợp đồng thuê nháp & dự thảo hợp đồng của Sales.
     */
    public function contracts(Request $request): View
    {
        $viewData = $this->getSalesViewData($request, 'contracts', 'Hợp đồng thuê & Dự thảo hợp đồng');
        return view('sales.contracts', $viewData);
    }

    /**
     * Chuẩn bị dữ liệu hiển thị an toàn cho Sales và Super Admin (Case 1).
     */
    protected function getSalesViewData(Request $request, string $activeTab, string $breadcrumb): array
    {
        $user = $request->attributes->get('current_web_user') ?? auth()->user();
        if (!$user) {
            $user = Sale::where('email', 'sales.demo@officeleasing.vn')->first();
        }

        $viewData = $this->portalDashboardService->getDashboardPortalData();

        $activeLeadsCount = 0;

        if ($user instanceof Sale) {
            // Scope phân quyền tòa nhà: Sales chỉ được xem và lập hợp đồng trên các tòa nhà được phân công
            $assignedBuildingIds = $user->buildings()->pluck('buildings.id')->toArray();
            $viewData['assignedBuildingIds'] = $assignedBuildingIds;
            $viewData['serverBuildings'] = collect($viewData['serverBuildings'] ?? [])->filter(function ($b) use ($assignedBuildingIds) {
                $bId = is_array($b) ? ($b['id'] ?? '') : ($b->id ?? '');
                return in_array($bId, $assignedBuildingIds);
            })->values();

            // Lấy danh sách Leads do chính Sales cá nhân này phụ trách
            $userLeads = $user->leads()
                ->with(['building', 'unit', 'assignedSales'])
                ->latest()
                ->get();
            $activeLeadsCount = $userLeads->whereNotIn('status', ['WON', 'LOST'])->count();

            // Luôn ghi đè dữ liệu Leads bằng danh sách cá nhân (không fallback dữ liệu toàn công ty)
            $viewData['leads'] = $userLeads;

            // Lọc hợp đồng cá nhân do chính Sales này tạo/chốt
            $userContracts = Contract::where('created_by_sales_id', $user->id)
                ->orWhereHas('tenant', fn ($t) => $t->where('created_by_sales_id', $user->id))
                ->with(['tenant', 'building', 'units'])
                ->latest()
                ->get();
            $viewData['recentContracts'] = $userContracts;

            // Xây dựng danh sách thông báo riêng cho Sales (Tòa nhà được phân công & Lead cá nhân)
            $salesNotifications = [];
            $assignedBuildings = $user->buildings()->latest('sale_buildings.assigned_at')->get();
            foreach ($assignedBuildings as $b) {
                $salesNotifications[] = [
                    'type' => 'building_assignment',
                    'icon' => 'fa-solid fa-building-circle-check',
                    'icon_bg' => '#eff6ff',
                    'icon_color' => '#2563eb',
                    'badge_text' => 'Tòa nhà phụ trách',
                    'badge_style' => 'background:#eff6ff;color:#1d4ed8;',
                    'title' => "Bạn được phân công phụ trách: {$b->name}",
                    'subtitle' => "Mã: {$b->building_code} • {$b->address}",
                    'target_tab' => 'tab-sales-buildings',
                    'nav_id' => 'nav-sales-buildings',
                ];
            }
            foreach ($userLeads->take(3) as $l) {
                $salesNotifications[] = [
                    'type' => 'lead',
                    'icon' => 'fa-solid fa-filter-circle-dollar',
                    'icon_bg' => '#f0fdf4',
                    'icon_color' => '#16a34a',
                    'badge_text' => 'Lead cá nhân',
                    'badge_style' => 'background:#f0fdf4;color:#15803d;',
                    'title' => ($l->company_name ?: $l->contact_full_name),
                    'subtitle' => "Quan tâm: " . ($l->building?->name ?? 'Mặt bằng') . " • SĐT: " . ($l->phone ?? '---'),
                    'target_tab' => 'tab-sales-leads',
                    'nav_id' => 'nav-item-leads',
                ];
            }
            $viewData['notifications'] = $salesNotifications;
        } elseif ($user instanceof Admin && $user->is_super_admin) {
            // Case 1: Super Admin kiểm tra phân hệ Sales (Xem tổng hợp toàn công ty)
            $activeLeadsCount = count($viewData['leads'] ?? []);
        }

        $viewData['currentUserName'] = $user?->full_name ?? $user?->name ?? 'Phạm Kinh Doanh (Sales Demo)';
        $viewData['currentUserEmail'] = $user?->email ?? 'sales.demo@officeleasing.vn';
        $viewData['currentUserSub'] = 'Chuyên viên Leasing & Báo giá';
        $viewData['currentUserRoleLabel'] = 'CHUYÊN VIÊN SALES';
        $viewData['avatarInitial'] = 'KD';
        $viewData['roleBadge'] = 'Chuyên Viên Sales';
        $viewData['activeLeadsCount'] = $activeLeadsCount;
        $viewData['activeTab'] = $activeTab;
        $viewData['breadcrumb'] = $breadcrumb;

        return $viewData;
    }
}
