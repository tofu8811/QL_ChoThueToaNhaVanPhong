<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Tenant\StoreTenantRequest;
use App\Http\Requests\V1\Tenant\UpdateTenantRequest;
use App\Http\Requests\V1\Tenant\UploadLicenseRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller Quản trị Hồ sơ Khách thuê Doanh nghiệp (Tenants).
 */
class TenantApiController extends BaseApiController
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Danh sách khách thuê doanh nghiệp (phân trang & tìm kiếm).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $tenants = $this->tenantService->getTenants($request->all(), $perPage);

        return $this->paginateResponse($tenants, 'Lấy danh sách khách thuê thành công.');
    }

    /**
     * Tạo mới hồ sơ khách thuê doanh nghiệp.
     */
    public function store(StoreTenantRequest $request): JsonResponse
    {
        $salesId = $request->user()?->id;
        $tenant = $this->tenantService->createTenant($request->validated(), $salesId);

        return $this->createdResponse($tenant, 'Tạo mới hồ sơ khách thuê thành công.');
    }

    /**
     * Xem thông tin chi tiết một khách thuê.
     */
    public function show(string $id): JsonResponse
    {
        $tenant = $this->tenantService->getTenantDetail($id);

        return $this->successResponse($tenant, 'Lấy chi tiết hồ sơ khách thuê thành công.');
    }

    /**
     * Cập nhật thông tin hồ sơ pháp lý khách thuê.
     */
    public function update(UpdateTenantRequest $request, string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $updatedTenant = $this->tenantService->updateTenant($tenant, $request->validated());

        return $this->successResponse($updatedTenant, 'Cập nhật hồ sơ khách thuê thành công.');
    }

    /**
     * Vô hiệu hóa khách thuê (có kiểm tra ràng buộc tài chính & hợp đồng).
     */
    public function destroy(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $this->tenantService->deactivateTenant($tenant);

        return $this->successResponse(null, 'Vô hiệu hóa hồ sơ khách thuê thành công.');
    }

    /**
     * Tải lên tài liệu scan Giấy phép ĐKKD vào Private Bucket.
     */
    public function uploadLicense(UploadLicenseRequest $request, string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $path = $this->tenantService->uploadBusinessLicense($tenant, $request->file('file'));

        return $this->successResponse([
            'path' => $path,
            'tenant' => $tenant->fresh(),
        ], 'Tải lên Giấy chứng nhận ĐKKD thành công.');
    }

    /**
     * Lấy đường dẫn ký số (Signed URL) tải Giấy phép ĐKKD.
     */
    public function downloadLicense(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $url = $this->tenantService->getLicenseDownloadUrl($tenant);

        return $this->successResponse([
            'download_url' => $url,
            'expires_in_minutes' => 10,
        ], 'Tạo liên kết tải Giấy phép ĐKKD thành công.');
    }
}
