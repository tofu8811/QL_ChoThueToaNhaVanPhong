<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Sale\StoreSaleRequest;
use App\Http\Requests\V1\Sale\UpdateSaleRequest;
use App\Models\Admin;
use App\Services\SaleUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý tài khoản Nhân viên Sales (Bán hàng / Môi giới).
 * Module 5: Nhân sự & RBAC (Endpoints 77 - 81).
 */
class SaleUserApiController extends BaseApiController
{
    public function __construct(
        protected SaleUserService $saleUserService
    ) {}

    /**
     * 77. GET /api/v1/admin/users/sales: Danh sách Nhân viên Sales.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xem danh sách nhân viên Sales.', 403);
        }

        $perPage = (int) $request->input('per_page', 10);
        $sales = $this->saleUserService->getSaleList($request->all(), $perPage);

        return $this->paginateResponse($sales, 'Lấy danh sách nhân viên Sales thành công.');
    }

    /**
     * 78. POST /api/v1/admin/users/sales: Tạo mới Nhân viên Sales.
     */
    public function store(StoreSaleRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền tạo nhân viên Sales.', 403);
        }

        $sale = $this->saleUserService->createSale($request->validated(), $admin);

        return $this->successResponse($sale, 'Tạo mới nhân viên Sales thành công.', 201);
    }

    /**
     * 79. GET /api/v1/admin/users/sales/{id}: Chi tiết Nhân viên Sales (kèm thống kê KPI).
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $result = $this->saleUserService->getSaleDetail($id, $request->user());

        return $this->successResponse($result, 'Lấy chi tiết hồ sơ nhân viên Sales thành công.');
    }

    /**
     * 80. PUT /api/v1/admin/users/sales/{id}: Cập nhật Nhân viên Sales.
     */
    public function update(string $id, UpdateSaleRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền cập nhật nhân viên Sales.', 403);
        }

        $sale = $this->saleUserService->updateSale($id, $request->validated(), $admin);

        return $this->successResponse($sale, 'Cập nhật thông tin nhân viên Sales thành công.');
    }

    /**
     * 81. DELETE /api/v1/admin/users/sales/{id}: Xóa mềm Nhân viên Sales.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xóa nhân viên Sales.', 403);
        }

        $reAssignTo = $request->input('re_assign_to_sales_id');
        $this->saleUserService->deleteSale($id, $reAssignTo);

        return $this->successResponse(null, 'Xóa mềm nhân viên Sales thành công.');
    }
}
