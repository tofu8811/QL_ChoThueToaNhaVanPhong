<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BusinessException;
use App\Http\Requests\V1\Tenant\UpdateTenantProfileRequest;
use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller dành riêng cho Cổng thông tin Khách thuê (Tenant Portal).
 */
class TenantPortalApiController extends BaseApiController
{
    /**
     * Khách thuê tự xem hồ sơ doanh nghiệp của mình.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $tenant = $this->resolveTenant($request);

        return $this->successResponse($tenant, 'Lấy thông tin hồ sơ doanh nghiệp thành công.');
    }

    /**
     * Khách thuê tự cập nhật liên hệ (billing_email, phone, đổi password).
     */
    public function updateProfile(UpdateTenantProfileRequest $request): JsonResponse
    {
        $tenant = $this->resolveTenant($request);
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = $data['password']; // Sẽ được cast sang hashed trong Model
        } else {
            unset($data['password']);
        }

        $tenant->update($data);

        return $this->successResponse($tenant->fresh(), 'Cập nhật thông tin liên hệ thành công.');
    }

    /**
     * Danh sách hợp đồng thuê thuộc doanh nghiệp của khách đang đăng nhập.
     */
    public function getContracts(Request $request): JsonResponse
    {
        $tenant = $this->resolveTenant($request);
        $perPage = (int) $request->input('per_page', 10);

        $contracts = Contract::where('tenant_id', $tenant->id)
            ->with(['building:id,building_code,name,address', 'units:id,unit_code,name,area_sqm'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->paginateResponse($contracts, 'Lấy danh sách hợp đồng thuê thành công.');
    }

    /**
     * Chi tiết một hợp đồng cụ thể của khách thuê (Phòng chống IDOR).
     */
    public function getContractDetail(string $id, Request $request): JsonResponse
    {
        $tenant = $this->resolveTenant($request);

        $contract = Contract::where('id', $id)
            ->where('tenant_id', $tenant->id)
            ->with([
                'building',
                'contractUnits.unit',
                'images',
            ])
            ->first();

        if (!$contract) {
            throw new BusinessException('Hợp đồng không tồn tại hoặc bạn không có quyền truy cập.', 403);
        }

        return $this->successResponse($contract, 'Lấy chi tiết hợp đồng thành công.');
    }

    /**
     * Giải quyết đối tượng Tenant từ Request (Sanctum User hoặc Header ID dùng cho testing).
     */
    protected function resolveTenant(Request $request): Tenant
    {
        $user = $request->user();

        if ($user instanceof Tenant) {
            return $user;
        }

        // Hỗ trợ truyền tenant_id qua header nếu test không qua auth middleware
        $tenantId = $request->header('X-Tenant-Id') ?? $request->input('mock_tenant_id');
        if ($tenantId) {
            return Tenant::findOrFail($tenantId);
        }

        throw new BusinessException('Yêu cầu xác thực tài khoản Khách thuê (Tenant).', 401);
    }
}
