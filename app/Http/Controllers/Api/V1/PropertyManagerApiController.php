<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\PropertyManager\StorePropertyManagerRequest;
use App\Http\Requests\V1\PropertyManager\UpdatePropertyManagerRequest;
use App\Models\Admin;
use App\Services\PropertyManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý tài khoản Ban Quản lý & Kỹ thuật tòa nhà (PM).
 * Module 5: Nhân sự & RBAC (Endpoints 82 - 86).
 */
class PropertyManagerApiController extends BaseApiController
{
    public function __construct(
        protected PropertyManagerService $propertyManagerService
    ) {}

    /**
     * 82. GET /api/v1/admin/users/property-managers: Danh sách Ban Quản lý (PM).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xem danh sách Ban Quản lý.', 403);
        }

        $perPage = (int) $request->input('per_page', 10);
        $pms = $this->propertyManagerService->getPropertyManagerList($request->all(), $perPage);

        return $this->paginateResponse($pms, 'Lấy danh sách Ban Quản lý thành công.');
    }

    /**
     * 83. POST /api/v1/admin/users/property-managers: Tạo mới Ban Quản lý (PM).
     */
    public function store(StorePropertyManagerRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền tạo nhân sự Ban Quản lý.', 403);
        }

        $pm = $this->propertyManagerService->createPropertyManager($request->validated(), $admin);

        return $this->successResponse($pm, 'Tạo mới nhân sự Ban Quản lý thành công.', 201);
    }

    /**
     * 84. GET /api/v1/admin/users/property-managers/{id}: Chi tiết Ban Quản lý (PM).
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $result = $this->propertyManagerService->getPropertyManagerDetail($id, $request->user());

        return $this->successResponse($result, 'Lấy chi tiết hồ sơ Ban Quản lý thành công.');
    }

    /**
     * 85. PUT /api/v1/admin/users/property-managers/{id}: Cập nhật Ban Quản lý (PM).
     */
    public function update(string $id, UpdatePropertyManagerRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền cập nhật nhân sự Ban Quản lý.', 403);
        }

        $pm = $this->propertyManagerService->updatePropertyManager($id, $request->validated(), $admin);

        return $this->successResponse($pm, 'Cập nhật nhân sự Ban Quản lý thành công.');
    }

    /**
     * 86. DELETE /api/v1/admin/users/property-managers/{id}: Xóa mềm Ban Quản lý (PM).
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xóa nhân sự Ban Quản lý.', 403);
        }

        $this->propertyManagerService->deletePropertyManager($id);

        return $this->successResponse(null, 'Xóa mềm nhân sự Ban Quản lý thành công.');
    }
}
