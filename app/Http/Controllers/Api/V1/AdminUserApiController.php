<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Admin\StoreAdminRequest;
use App\Http\Requests\V1\Admin\UpdateAdminRequest;
use App\Models\Admin;
use App\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý tài khoản Quản trị viên (Admins).
 * Module 5 (Endpoints 72 - 76).
 */
class AdminUserApiController extends BaseApiController
{
    public function __construct(
        protected AdminUserService $adminUserService
    ) {}

    /**
     * 72. GET /api/v1/admin/users/admins: Danh sách Quản trị viên toàn hệ thống.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $admins = $this->adminUserService->getAdminList($request->all(), $perPage);

        return $this->paginateResponse($admins, 'Lấy danh sách quản trị viên thành công.');
    }

    /**
     * 73. POST /api/v1/admin/users/admins: Tạo mới tài khoản Quản trị viên.
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        $admin = $this->adminUserService->createAdmin($request->validated());

        return $this->successResponse($admin, 'Tạo tài khoản quản trị viên mới thành công.', 201);
    }

    /**
     * 74. GET /api/v1/admin/users/admins/{id}: Chi tiết tài khoản Quản trị viên theo UUID.
     */
    public function show(string $id): JsonResponse
    {
        $admin = $this->adminUserService->getAdminById($id);

        return $this->successResponse($admin, 'Lấy chi tiết tài khoản quản trị viên thành công.');
    }

    /**
     * 75. PUT /api/v1/admin/users/admins/{id}: Cập nhật tài khoản Quản trị viên.
     */
    public function update(string $id, UpdateAdminRequest $request): JsonResponse
    {
        /** @var Admin $currentAdmin */
        $currentAdmin = $request->user();

        $admin = $this->adminUserService->updateAdmin($id, $request->validated(), $currentAdmin);

        return $this->successResponse($admin, 'Cập nhật tài khoản quản trị viên thành công.');
    }

    /**
     * 76. DELETE /api/v1/admin/users/admins/{id}: Xóa mềm tài khoản Quản trị viên.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        /** @var Admin $currentAdmin */
        $currentAdmin = $request->user();

        $this->adminUserService->deleteAdmin($id, $currentAdmin);

        return $this->successResponse(null, 'Xóa tài khoản quản trị viên thành công.');
    }

    /**
     * API tự động tính toán mã nhân viên kế tiếp theo vai trò từ CSDL.
     * Hỗ trợ Sales, Ban Quản Lý (PM) và Kế Toán.
     */
    public function nextEmployeeCode(Request $request): JsonResponse
    {
        $role = strtolower((string) $request->input('role', 'sales'));

        $nextCode = match ($role) {
            'sales', 'sale' => (function () {
                $codes = \App\Models\Sale::withTrashed()->whereNotNull('employee_code')->pluck('employee_code');
                $max = 0;
                foreach ($codes as $c) {
                    if (preg_match('/(\d+)$/', (string) $c, $m)) {
                        $n = (int) $m[1];
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                return sprintf('NV-SALE-%03d', $max + 1);
            })(),
            'pm', 'property_manager', 'property-manager' => (function () {
                $codes = \App\Models\PropertyManager::withTrashed()->whereNotNull('employee_code')->pluck('employee_code');
                $max = 0;
                foreach ($codes as $c) {
                    if (preg_match('/(\d+)$/', (string) $c, $m)) {
                        $n = (int) $m[1];
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                return sprintf('NV-BQL-%03d', $max + 1);
            })(),
            'accountant', 'accountants', 'kt' => (function () {
                $codes = \App\Models\Accountant::withTrashed()->whereNotNull('employee_code')->pluck('employee_code');
                $max = 0;
                foreach ($codes as $c) {
                    if (preg_match('/(\d+)$/', (string) $c, $m)) {
                        $n = (int) $m[1];
                        if ($n > $max) {
                            $max = $n;
                        }
                    }
                }
                return sprintf('NV-KT-%03d', $max + 1);
            })(),
            default => null,
        };

        if (!$nextCode) {
            return $this->errorResponse('Vai trò không hợp lệ để tạo mã nhân viên.', 400);
        }

        return $this->successResponse(['next_code' => $nextCode], 'Lấy mã nhân viên tiếp theo thành công.');
    }
}
