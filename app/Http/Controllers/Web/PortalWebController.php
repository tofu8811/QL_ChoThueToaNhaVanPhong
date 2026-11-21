<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Services\BuildingService;
use App\Services\ContractService;
use App\Services\PortalDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Skinny Controller điều phối giao diện Web Portal & Không gian Quản trị BMS (HTTP Layer).
 * Ủy thác 100% logic tính toán nghiệp vụ và truy vấn thống kê cho tầng Service (BLL).
 */
class PortalWebController extends Controller
{
    /**
     * Khởi tạo Controller với các Service nghiệp vụ.
     */
    public function __construct(
        protected BuildingService $buildingService,
        protected PortalDashboardService $portalDashboardService
    ) {}

    /**
     * Hiển thị Trang chủ Cổng thông tin (Public Portal) & Không gian Quản trị BMS.
     *
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        $viewData = $this->portalDashboardService->getDashboardPortalData();

        return view('portal.index', $viewData);
    }

    /**
     * API tính toán dự toán chi phí thuê văn phòng từ tầng Service (BLL).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function calculateQuoteEstimate(Request $request): JsonResponse
    {
        try {
            $price = (float) ($request->input('price_vnd') ?? ($request->input('price_usd') ?? 600000));
            $serviceFee = (float) ($request->input('service_fee_vnd') ?? ($request->input('service_fee_usd') ?? 120000));

            $data = $this->portalDashboardService->calculateQuoteEstimate(
                $price,
                (float) $request->input('area_sqm', 200),
                $serviceFee,
                (int) $request->input('deposit_months', 3),
                (string) $request->input('building_name', 'Tòa nhà văn phòng')
            );

            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tính toán dự toán báo giá tại PortalWebController', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Không thể tính dự toán chi phí tại thời điểm này.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Kết xuất file CSV Báo giá thuê văn phòng từ dữ liệu của PortalDashboardService.
     *
     * @param  Request  $request
     * @return StreamedResponse
     */
    public function exportQuoteCsv(Request $request): StreamedResponse
    {
        $rows = $this->portalDashboardService->getQuoteCsvRows($request->only([
            'price_usd',
            'area_sqm',
            'service_fee_usd',
            'deposit_months',
            'building_name',
        ]));

        $fileName = 'Bao_Gia_Thue_Van_Phong_' . now()->format('Y-m-d') . '.csv';

        return $this->streamCsvDownload($rows, $fileName);
    }

    /**
     * Kết xuất file CSV Báo cáo Tổng quan Vận hành & Kinh doanh từ PortalDashboardService.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function exportDashboardCsv(Request $request): mixed
    {
        if (!$this->checkWebAuth($request)) {
            return redirect('/');
        }

        $rows = $this->portalDashboardService->getDashboardCsvRows();
        $fileName = 'Bao_Cao_Van_Hanh_Office_' . now()->format('Y-m-d') . '.csv';

        return $this->streamCsvDownload($rows, $fileName);
    }

    /**
     * Hiển thị trang đăng ký nhận báo giá và đặt lịch khảo sát chuẩn SEO SSR.
     *
     * @param  Request  $request
     * @return View
     */
    public function quotePage(Request $request): View
    {
        return view('pages.quote');
    }

    /**
     * Hiển thị trang quản lý hợp đồng thuê văn phòng chuẩn SEO SSR (Module 3).
     *
     * @param  Request  $request
     * @return mixed
     */
    public function contractsPage(Request $request): mixed
    {
        if (!$this->checkWebAuth($request)) {
            return redirect('/');
        }

        return view('pages.contracts');
    }

    /**
     * Hiển thị bản in / xuất PDF Hợp đồng thuê văn phòng chuẩn A4 (Module 3).
     */
    public function printContract(string $id, ContractService $contractService, Request $request): mixed
    {
        if (!$this->checkWebAuth($request)) {
            return redirect('/');
        }

        $data = $contractService->getContractPrintData($id);
        return view('contracts.print-template', $data);
    }

    /**
     * Hiển thị trang quản lý hồ sơ khách thuê doanh nghiệp chuẩn SEO SSR (Module 3).
     *
     * @param  Request  $request
     * @return mixed
     */
    public function tenantsPage(Request $request): mixed
    {
        if (!$this->checkWebAuth($request)) {
            return redirect('/');
        }

        return view('pages.tenants');
    }

    /**
     * Hiển thị trang quản lý phễu khách hàng tiềm năng CRM & tiếp nhận Lead (Module 2).
     *
     * @param  Request  $request
     * @return mixed
     */
    public function leadsPage(Request $request): mixed
    {
        if (!$this->checkWebAuth($request)) {
            return redirect('/');
        }

        return view('pages.leads');
    }

    /**
     * Hiển thị cổng thông tin không gian làm việc khách thuê Tenant Portal SSR (Module 3).
     *
     * @param  Request  $request
     * @return mixed
     */
    public function tenantPortalPage(Request $request): mixed
    {
        $this->checkWebAuth($request);

        // Xác định Tenant ID từ query param, cookie hoặc tài khoản đang đăng nhập
        $tenantId = $request->query('tenant_id')
            ?? $request->cookie('tenant_id')
            ?? (\Illuminate\Support\Facades\Auth::guard('tenant')->user()?->id ?? \Illuminate\Support\Facades\Auth::user()?->id);

        if (!$tenantId) {
            $demoTenant = \App\Models\Tenant::where('login_email', 'tenant.demo@officeleasing.vn')->first()
                ?? \App\Models\Tenant::where('status', 'ACTIVE')->first();
            $tenantId = $demoTenant?->id;
        }

        return view('pages.tenant-portal', ['tenantId' => $tenantId]);
    }

    /**
     * Kiểm tra phiên xác thực người dùng cho các trang Web SSR nội bộ.
     */
    protected function checkWebAuth(Request $request): bool
    {
        if (auth()->check() || auth('sanctum')->check()) {
            return true;
        }

        $token = $request->bearerToken() ?? $request->cookie('auth_token');
        if ($token) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                if ($accessToken->tokenable instanceof \App\Models\Tenant) {
                    \Illuminate\Support\Facades\Auth::guard('tenant')->setUser($accessToken->tokenable);
                }
                \Illuminate\Support\Facades\Auth::setUser($accessToken->tokenable);
                return true;
            }
        }

        return false;
    }

    /**
     * Hiển thị trang chi tiết Tòa nhà Văn phòng chuẩn SEO SSR (Module 1).
     *
     * @param  string  $slug
     * @return View
     */
    public function showBuilding(string $slug): View
    {
        try {
            $building = $this->buildingService->getPublicBuildingBySlug($slug);
        } catch (\Throwable $e) {
            Log::warning('Fallback truy vấn chi tiết tòa nhà theo ID/Slug tại PortalWebController', [
                'slug' => $slug,
                'message' => $e->getMessage(),
            ]);

            $building = Building::query()
                ->where('slug', $slug)
                ->orWhere('id', $slug)
                ->with(['images', 'primaryImage', 'units'])
                ->firstOrFail();
        }

        return view('pages.building-detail', compact('building'));
    }

    /**
     * Helper phản hồi StreamedResponse chuẩn UTF-8 BOM cho file CSV.
     *
     * @param  list<list<string>>  $rows
     * @param  string  $fileName
     * @return StreamedResponse
     */
    protected function streamCsvDownload(array $rows, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
