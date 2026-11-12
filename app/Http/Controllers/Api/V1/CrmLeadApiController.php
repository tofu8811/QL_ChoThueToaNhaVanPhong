<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Lead\AssignLeadSalesRequest;
use App\Http\Requests\V1\Lead\ConvertLeadToTenantRequest;
use App\Http\Requests\V1\Lead\StoreCrmLeadRequest;
use App\Http\Requests\V1\Lead\UpdateLeadRequest;
use App\Http\Requests\V1\Lead\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý Khách hàng tiềm năng & Tiến trình CRM (CRM Lead API).
 */
class CrmLeadApiController extends BaseApiController
{
    public function __construct(
        protected LeadService $leadService
    ) {}

    // Lấy danh sách khách hàng tiềm năng theo Pipeline CRM
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $leads = $this->leadService->getLeads($request->all(), $request->user(), $perPage);

        return $this->paginateResponse($leads, 'Lấy danh sách Lead CRM thành công.');
    }

    // Tạo mới hồ sơ Lead thủ công từ CRM
    public function store(StoreCrmLeadRequest $request): JsonResponse
    {
        $lead = $this->leadService->storeCrmLead($request->validated(), $request->user());

        return $this->createdResponse(
            $lead->load(['building', 'unit', 'assignedSales']),
            'Khởi tạo hồ sơ khách hàng tiềm năng thành công.'
        );
    }

    // Xem chi tiết hồ sơ Lead và lịch sử chăm sóc
    public function show(string $id): JsonResponse
    {
        $lead = Lead::with(['building', 'unit', 'assignedSales', 'convertedTenant', 'activities.sales'])
            ->findOrFail($id);

        return $this->successResponse($lead, 'Lấy thông tin chi tiết Lead thành công.');
    }

    // Cập nhật thông tin nhu cầu của Lead
    public function update(UpdateLeadRequest $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $updated = $this->leadService->updateLead($lead, $request->validated());

        return $this->successResponse($updated, 'Cập nhật thông tin Lead thành công.');
    }

    // Xóa mềm hồ sơ Lead
    public function destroy(string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->leadService->deleteLead($lead);

        return $this->successResponse(null, "Đã xóa Lead [{$lead->contact_full_name}] thành công.");
    }

    // Cập nhật trạng thái tiến trình Pipeline của Lead
    public function updateStatus(UpdateLeadStatusRequest $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $updated = $this->leadService->updateLeadStatus(
            $lead,
            $request->input('status'),
            $request->input('lost_reason_category'),
            $request->input('lost_reason_detail')
        );

        return $this->successResponse($updated, 'Cập nhật trạng thái Pipeline thành công.');
    }

    // Điều phối hoặc phân công lại Sales phụ trách Lead
    public function assign(AssignLeadSalesRequest $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $updated = $this->leadService->assignSales(
            $lead,
            $request->input('sales_id'),
            $request->input('assignment_note')
        );

        return $this->successResponse($updated, 'Điều phối nhân sự phụ trách Lead thành công.');
    }

    // Chuyển đổi Lead thành công sang Khách thuê chính thức
    public function convertToTenant(ConvertLeadToTenantRequest $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $tenant = $this->leadService->convertToTenant($lead, $request->validated());

        return $this->createdResponse($tenant, 'Chuyển đổi Lead sang Khách thuê chính thức thành công.');
    }
}
