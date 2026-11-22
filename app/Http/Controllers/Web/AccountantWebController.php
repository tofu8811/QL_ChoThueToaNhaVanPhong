<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accountant;
use App\Models\Admin;
use App\Services\PortalDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller chuyên trách Phân hệ Kế toán & Công nợ (Accountant Portal).
 * Quản lý Thông báo cước (Debit Notes), Đối soát UNC và Kế toán công nợ tòa nhà.
 */
class AccountantWebController extends Controller
{
    public function __construct(
        protected PortalDashboardService $portalDashboardService
    ) {}

    /**
     * Bảng điều khiển Tổng quan Tài chính & Công nợ Vận hành.
     */
    public function dashboard(Request $request): View
    {
        $viewData = $this->getAccountantViewData($request, 'dashboard', 'Tổng quan tài chính & công nợ tòa nhà');
        return view('accountant.dashboard', $viewData);
    }

    /**
     * Quản lý Thông báo phí (Debit Notes) và Đối soát Ủy nhiệm chi (UNC).
     */
    public function billing(Request $request): View
    {
        $viewData = $this->getAccountantViewData($request, 'billing', 'Thông báo phí & Đối soát UNC');
        return view('accountant.billing', $viewData);
    }

    /**
     * Hợp đồng & Lịch trình thanh toán / Tăng giá định kỳ.
     */
    public function contracts(Request $request): View
    {
        $viewData = $this->getAccountantViewData($request, 'contracts', 'Hợp đồng & Lịch thanh toán tiền thuê');
        return view('accountant.contracts', $viewData);
    }

    /**
     * Sổ danh bạ khách thuê và thông tin xuất hóa đơn VAT.
     */
    public function tenants(Request $request): View
    {
        $viewData = $this->getAccountantViewData($request, 'tenants', 'Sổ danh bạ khách thuê doanh nghiệp');
        return view('accountant.tenants', $viewData);
    }

    /**
     * Chuẩn bị dữ liệu hiển thị an toàn cho Kế toán và Super Admin.
     */
    protected function getAccountantViewData(Request $request, string $activeTab, string $breadcrumb): array
    {
        $user = $request->attributes->get('current_web_user') ?? auth()->user();
        $viewData = $this->portalDashboardService->getDashboardPortalData();

        $viewData['currentUserName'] = $user?->full_name ?? $user?->name ?? 'Lê Thị Thu';
        $viewData['currentUserEmail'] = $user?->email ?? 'accountant.thu@officeleasing.vn';
        $viewData['currentUserSub'] = 'Kế toán công nợ & Đối soát UNC';
        $viewData['currentUserRoleLabel'] = 'KẾ TOÁN';
        $viewData['avatarInitial'] = 'KT';
        $viewData['roleBadge'] = 'Kế Toán';
        $viewData['activeTab'] = $activeTab;
        $viewData['breadcrumb'] = $breadcrumb;

        return $viewData;
    }
}
