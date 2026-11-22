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
        $viewData = $this->portalDashboardService->getDashboardPortalData();

        $assignedBuildingIds = [];
        $assignedBuildingsCount = 0;

        if ($user instanceof PropertyManager) {
            $assignedBuildings = $user->buildings()->get();
            $assignedBuildingIds = $assignedBuildings->pluck('id')->toArray();
            $assignedBuildingsCount = count($assignedBuildingIds);

            // Lọc dữ liệu tòa nhà nếu có phân công
            if ($assignedBuildingsCount > 0) {
                $viewData['serverBuildings'] = $viewData['serverBuildings']->filter(function ($b) use ($assignedBuildingIds) {
                    return in_array($b['id'] ?? '', $assignedBuildingIds);
                })->values();
            }
        } elseif ($user instanceof Admin && $user->is_super_admin) {
            // Case 1: Super Admin kiểm tra phân hệ PM (Xem toàn bộ hoặc mẫu đầu tiên)
            $assignedBuildingsCount = count($viewData['serverBuildings'] ?? []);
        }

        $viewData['currentUserName'] = $user?->full_name ?? $user?->name ?? 'Đỗ Minh Quang';
        $viewData['currentUserEmail'] = $user?->email ?? 'pm.quang@officeleasing.vn';
        $viewData['currentUserSub'] = 'Ban Quản Lý • Vận hành & Kỹ thuật';
        $viewData['currentUserRoleLabel'] = 'BAN QUẢN LÝ';
        $viewData['avatarInitial'] = 'PM';
        $viewData['roleBadge'] = 'Ban Quản Lý';
        $viewData['assignedBuildingsCount'] = $assignedBuildingsCount ?: 1;
        $viewData['activeTab'] = $activeTab;
        $viewData['breadcrumb'] = $breadcrumb;

        return $viewData;
    }
}
