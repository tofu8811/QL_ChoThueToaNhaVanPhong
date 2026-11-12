<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Accountant\StoreAccountantRequest;
use App\Http\Requests\V1\Accountant\UpdateAccountantRequest;
use App\Models\Admin;
use App\Services\AccountantUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý tài khoản Kế toán công nợ tòa nhà (Accountants).
 * Module 5: Nhân sự & RBAC (Endpoints 87 - 91).
 */
class AccountantUserApiController extends BaseApiController
{
    public function __construct(
        protected AccountantUserService $accountantUserService
    ) {}

    /**
     * 87. GET /api/v1/admin/users/accountants: Danh sách Nhân viên Kế toán.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xem danh sách Kế toán.', 403);
        }

        $perPage = (int) $request->input('per_page', 10);
        $accountants = $this->accountantUserService->getAccountantList($request->all(), $perPage);

        return $this->paginateResponse($accountants, 'Lấy danh sách Kế toán thành công.');
    }

    /**
     * 88. POST /api/v1/admin/users/accountants: Tạo mới Kế toán.
     */
    public function store(StoreAccountantRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền tạo nhân sự Kế toán.', 403);
        }

        $accountant = $this->accountantUserService->createAccountant($request->validated(), $admin);

        return $this->successResponse($accountant, 'Tạo mới nhân sự Kế toán thành công.', 201);
    }

    /**
     * 89. GET /api/v1/admin/users/accountants/{id}: Chi tiết Nhân viên Kế toán.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $result = $this->accountantUserService->getAccountantDetail($id, $request->user());

        return $this->successResponse($result, 'Lấy chi tiết hồ sơ Kế toán thành công.');
    }

    /**
     * 90. PUT /api/v1/admin/users/accountants/{id}: Cập nhật Kế toán.
     */
    public function update(string $id, UpdateAccountantRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền cập nhật nhân sự Kế toán.', 403);
        }

        $accountant = $this->accountantUserService->updateAccountant($id, $request->validated(), $admin);

        return $this->successResponse($accountant, 'Cập nhật nhân sự Kế toán thành công.');
    }

    /**
     * 91. DELETE /api/v1/admin/users/accountants/{id}: Xóa mềm Kế toán.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xóa nhân sự Kế toán.', 403);
        }

        $this->accountantUserService->deleteAccountant($id);

        return $this->successResponse(null, 'Xóa mềm nhân sự Kế toán thành công.');
    }
}
