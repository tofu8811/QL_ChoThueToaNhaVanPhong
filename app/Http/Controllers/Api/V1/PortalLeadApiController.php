<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Lead\StorePortalLeadRequest;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;

/**
 * Controller tiếp nhận yêu cầu từ Cổng thông tin công khai (Portal Lead API).
 */
class PortalLeadApiController extends BaseApiController
{
    public function __construct(
        protected LeadService $leadService
    ) {}

    // Tiếp nhận yêu cầu báo giá / Site tour từ khách vãng lai trên Website
    public function store(StorePortalLeadRequest $request): JsonResponse
    {
        $lead = $this->leadService->storePortalLead($request->validated());

        return $this->createdResponse(
            $lead->load(['building', 'unit', 'assignedSales']),
            'Gửi yêu cầu tư vấn thành công. Chuyên viên kinh doanh sẽ liên hệ sớm nhất.'
        );
    }
}
