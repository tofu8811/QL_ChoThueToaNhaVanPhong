<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PortalDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller điều phối Giao diện Quản trị Vận hành Tòa nhà (Admin BMS).
 * Kế thừa dữ liệu thống kê từ PortalDashboardService (BLL).
 */
class AdminWebController extends Controller
{
    public function __construct(
        protected PortalDashboardService $portalDashboardService
    ) {}

    // Hiển thị Bảng điều khiển Quản trị Vận hành Tòa nhà (Admin BMS Dashboard)
    public function index(Request $request): View
    {
        $viewData = $this->portalDashboardService->getDashboardPortalData();

        return view('admin.index', $viewData);
    }
}
