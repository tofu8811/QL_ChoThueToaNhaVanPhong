<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\PortalDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller chuyên trách Phân hệ Quản trị & Vận hành Tòa nhà (Super Admin & Admin Portal).
 */
class AdminWebController extends Controller
{
    public function __construct(
        protected PortalDashboardService $portalDashboardService
    ) {}

    /**
     * Bảng điều khiển Tổng quan Quản trị Vận hành (Admin BMS Dashboard).
     */
    public function dashboard(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'dashboard', 'Tổng quan vận hành toàn hệ thống');
        return view('admin.dashboard', $viewData);
    }

    /**
     * Quản lý Danh mục Tòa nhà & Mặt bằng.
     */
    public function buildings(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'buildings', 'Quản lý tài nguyên mặt bằng tòa nhà');
        return view('admin.buildings', $viewData);
    }

    /**
     * Quản lý Hợp đồng thuê & Biên bản bàn giao.
     */
    public function contracts(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'contracts', 'Hợp đồng thuê & Biên bản bàn giao');
        return view('admin.contracts', $viewData);
    }

    /**
     * Quản lý Danh bạ Khách thuê doanh nghiệp.
     */
    public function tenants(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'tenants', 'Quản lý khách hàng thuê');
        return view('admin.tenants', $viewData);
    }

    /**
     * Quản lý Ticket sự cố & Thi công kỹ thuật.
     */
    public function tickets(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'tickets', 'Tiếp nhận & Xử lý Ticket sự cố');
        return view('admin.tickets', $viewData);
    }

    /**
     * Quản lý Kế toán & Công nợ toàn hệ thống.
     */
    public function billing(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'billing', 'Kế toán & Công nợ tòa nhà');
        return view('admin.billing', $viewData);
    }

    /**
     * Quản trị Nhân sự & Phân quyền RBAC (Chỉ dành riêng cho Super Admin).
     */
    public function staff(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'staff', 'Quản trị nhân sự & Phân quyền RBAC');
        return view('admin.staff', $viewData);
    }

    /**
     * Thiết lập Cấu hình Email SMTP.
     */
    public function mailSettings(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'mail-settings', 'Cấu hình Email hệ thống');
        return view('admin.mail-settings', $viewData);
    }

    // Quản lý Phễu khách hàng tiềm năng & Điều phối Lead CRM
    public function leads(Request $request): View
    {
        $viewData = $this->getAdminViewData($request, 'leads', 'Quản lý Leads khách hàng & Pipeline CRM');
        return view('admin.leads', $viewData);
    }

    /**
     * Tuyến điều hướng tương thích ngược (/admin).
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    /**
     * Chuẩn bị dữ liệu hiển thị đồng bộ cho Phân hệ Admin.
     */
    protected function getAdminViewData(Request $request, string $activeTab, string $breadcrumb): array
    {
        $user = $request->attributes->get('current_web_user') ?? auth()->user();
        $viewData = $this->portalDashboardService->getDashboardPortalData();

        $isSuper = ($user instanceof Admin && $user->is_super_admin);

        $viewData['currentUserName'] = $user?->name ?? ($isSuper ? 'Nguyễn Quản Trị Hệ Thống' : 'Phạm Quản Trị');
        $viewData['currentUserEmail'] = $user?->email ?? 'admin.demo@officeleasing.vn';
        $viewData['currentUserSub'] = $isSuper ? 'Super Admin • Toàn hệ thống' : 'Quản trị viên • Vận hành';
        $viewData['currentUserRoleLabel'] = $isSuper ? 'SUPER ADMIN' : 'ADMIN';
        $viewData['avatarInitial'] = $isSuper ? 'SA' : 'AD';
        $viewData['roleBadge'] = $isSuper ? 'Super Admin' : 'Admin';
        $viewData['activeTab'] = $activeTab;
        $viewData['breadcrumb'] = $breadcrumb;

        return $viewData;
    }
}
