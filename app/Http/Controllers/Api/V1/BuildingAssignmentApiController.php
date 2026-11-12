<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\BuildingAssignment\AssignAccountantRequest;
use App\Http\Requests\V1\BuildingAssignment\AssignPropertyManagerRequest;
use App\Http\Requests\V1\BuildingAssignment\AssignSalesRequest;
use App\Models\Admin;
use App\Services\BuildingAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý Phân công Tòa nhà & RBAC (Building Assignments).
 * Module 5: Xác Thực, Quản Trị Nhân Sự & Phân Quyền Tòa Nhà (Endpoints 92 - 98).
 */
class BuildingAssignmentApiController extends BaseApiController
{
    public function __construct(
        protected BuildingAssignmentService $assignmentService
    ) {}

    /**
     * 92. GET /api/v1/admin/buildings/{buildingId}/assignments
     * Xem danh sách toàn bộ đội ngũ nhân sự đang phụ trách tòa nhà.
     */
    public function getAssignments(Request $request, string $buildingId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền xem danh sách nhân sự phụ trách tòa nhà.', 403);
        }

        $data = $this->assignmentService->getAssignments($buildingId);

        return $this->successResponse($data, 'Lấy danh sách nhân sự phụ trách tòa nhà thành công.');
    }

    /**
     * 93. POST /api/v1/admin/buildings/{buildingId}/assignments/sales
     * Gán một hoặc nhiều nhân viên Sales vào danh sách phụ trách bán mặt bằng tại tòa nhà.
     */
    public function assignSales(AssignSalesRequest $request, string $buildingId): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền phân công Sales vào tòa nhà.', 403);
        }

        $salesIds = (array) $request->input('sales_ids', []);
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        $result = $this->assignmentService->assignSales($buildingId, $salesIds, $isActive, $admin);

        return $this->successResponse($result, 'Phân công nhân viên Sales vào tòa nhà thành công.', 201);
    }

    /**
     * 94. DELETE /api/v1/admin/buildings/{buildingId}/assignments/sales/{saleId}
     * Gỡ bỏ phân công Sales khỏi tòa nhà.
     */
    public function removeSales(Request $request, string $buildingId, string $saleId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền gỡ bỏ phân công Sales khỏi tòa nhà.', 403);
        }

        $this->assignmentService->removeSales($buildingId, $saleId);

        return $this->successResponse(null, 'Đã gỡ bỏ nhân viên Sales khỏi danh sách phụ trách tòa nhà.');
    }

    /**
     * 95. POST /api/v1/admin/buildings/{buildingId}/assignments/property-managers
     * Gán Ban quản lý vào tòa nhà (kèm cờ is_head_manager nếu là Trưởng ban quản lý tòa nhà).
     */
    public function assignPropertyManager(AssignPropertyManagerRequest $request, string $buildingId): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền phân công Ban quản lý vào tòa nhà.', 403);
        }

        $pmId = (string) $request->input('pm_id');
        $isPrimary = $request->boolean('is_primary', false);

        $result = $this->assignmentService->assignPropertyManager($buildingId, $pmId, $isPrimary, $admin);

        return $this->successResponse($result, 'Phân công Ban quản lý vào tòa nhà thành công.', 201);
    }

    /**
     * 96. DELETE /api/v1/admin/buildings/{buildingId}/assignments/property-managers/{pmId}
     * Gỡ bỏ phân công Ban quản lý khỏi tòa nhà.
     */
    public function removePropertyManager(Request $request, string $buildingId, string $pmId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền gỡ bỏ phân công Ban quản lý khỏi tòa nhà.', 403);
        }

        $this->assignmentService->removePropertyManager($buildingId, $pmId);

        return $this->successResponse(null, 'Đã gỡ bỏ Ban quản lý khỏi danh sách phụ trách tòa nhà.');
    }

    /**
     * 97. POST /api/v1/admin/buildings/{buildingId}/assignments/accountants
     * Gán Kế toán phụ trách theo dõi công nợ, xuất Debit Note cho tòa nhà.
     */
    public function assignAccountant(AssignAccountantRequest $request, string $buildingId): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();
        if (!($admin instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền phân công Kế toán vào tòa nhà.', 403);
        }

        $accountantIds = (array) $request->input('accountant_ids', []);

        $result = $this->assignmentService->assignAccountant($buildingId, $accountantIds, $admin);

        return $this->successResponse($result, 'Phân công Kế toán vào tòa nhà thành công.', 201);
    }

    /**
     * 98. DELETE /api/v1/admin/buildings/{buildingId}/assignments/accountants/{accountantId}
     * Gỡ bỏ phân công Kế toán khỏi tòa nhà.
     */
    public function removeAccountant(Request $request, string $buildingId, string $accountantId): JsonResponse
    {
        $user = $request->user();
        if (!($user instanceof Admin)) {
            return $this->errorResponse('Chỉ Quản trị viên mới có quyền gỡ bỏ phân công Kế toán khỏi tòa nhà.', 403);
        }

        $this->assignmentService->removeAccountant($buildingId, $accountantId);

        return $this->successResponse(null, 'Đã gỡ bỏ Kế toán khỏi danh sách phụ trách tòa nhà.');
    }
}
