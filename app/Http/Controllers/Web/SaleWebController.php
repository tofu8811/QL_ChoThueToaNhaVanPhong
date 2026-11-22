<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
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
     * Chuẩn bị dữ liệu hiển thị an toàn cho Sales và Super Admin (Case 1).
     */
    protected function getSalesViewData(Request $request, string $activeTab, string $breadcrumb): array
    {
        $user = $request->attributes->get('current_web_user') ?? auth()->user();
        $viewData = $this->portalDashboardService->getDashboardPortalData();

        $activeLeadsCount = 0;

        if ($user instanceof Sale) {
            $userLeads = $user->leads()->get();
            $activeLeadsCount = $userLeads->whereNotIn('status', ['WON', 'LOST'])->count();
            if ($userLeads->count() > 0) {
                $viewData['leads'] = $userLeads;
            }
        } elseif ($user instanceof Admin && $user->is_super_admin) {
            // Case 1: Super Admin kiểm tra phân hệ Sales (Xem tổng hợp toàn công ty)
            $activeLeadsCount = count($viewData['leads'] ?? []);
        }

        $viewData['currentUserName'] = $user?->full_name ?? $user?->name ?? 'Trần Quốc Huy';
        $viewData['currentUserEmail'] = $user?->email ?? 'sales.huy@officeleasing.vn';
        $viewData['currentUserSub'] = 'Chuyên viên Leasing & Báo giá';
        $viewData['currentUserRoleLabel'] = 'CHUYÊN VIÊN SALES';
        $viewData['avatarInitial'] = 'SL';
        $viewData['roleBadge'] = 'Chuyên Viên Sales';
        $viewData['activeLeadsCount'] = $activeLeadsCount ?: 3;
        $viewData['activeTab'] = $activeTab;
        $viewData['breadcrumb'] = $breadcrumb;

        return $viewData;
    }
}
