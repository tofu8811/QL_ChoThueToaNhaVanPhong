<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Lead\StoreLeadActivityRequest;
use App\Models\Lead;
use App\Services\LeadActivityService;
use Illuminate\Http\JsonResponse;

/**
 * Controller quản lý Dòng thời gian & Hoạt động chăm sóc Lead (Lead Activity API).
 */
class LeadActivityApiController extends BaseApiController
{
    public function __construct(
        protected LeadActivityService $activityService
    ) {}

    // Lấy dòng thời gian lịch sử tương tác của Lead
    public function index(string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $activities = $this->activityService->getActivities($lead);

        return $this->successResponse($activities, 'Lấy lịch sử chăm sóc Lead thành công.');
    }

    // Ghi nhật ký hoạt động tương tác chăm sóc Lead
    public function store(StoreLeadActivityRequest $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $activity = $this->activityService->logActivity($lead, $request->validated(), $request->user()?->id);

        return $this->createdResponse($activity, 'Ghi nhận nhật ký chăm sóc thành công.');
    }
}
