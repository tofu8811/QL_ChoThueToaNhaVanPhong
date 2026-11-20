<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Lead\StoreLeadActivityRequest;
use App\Models\Lead;
use App\Services\LeadActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller quản lý Dòng thời gian & Hoạt động chăm sóc Lead (Lead Activity API).
 */
class LeadActivityApiController extends BaseApiController
{
    public function __construct(
        protected LeadActivityService $activityService
    ) {}

    // Lấy dòng thời gian lịch sử tương tác của Lead
    public function index(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorizeSalesAccess($request->user(), $lead);

        $activities = $this->activityService->getActivities($lead);

        return $this->successResponse($activities, 'Lấy lịch sử chăm sóc Lead thành công.');
    }

    // Ghi nhật ký hoạt động tương tác chăm sóc Lead
    public function store(StoreLeadActivityRequest $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorizeSalesAccess($request->user(), $lead);

        $salesId = $request->user() instanceof \App\Models\Sale ? $request->user()->id : null;
        $activity = $this->activityService->logActivity($lead, $request->validated(), $salesId);

        return $this->createdResponse($activity, 'Ghi nhận nhật ký chăm sóc thành công.');
    }

    protected function authorizeSalesAccess(mixed $user, Lead $lead): void
    {
        if ($user instanceof \App\Models\Sale) {
            $isAssigned = $lead->assigned_sales_id === $user->id;
            $isBuildingAssigned = $lead->building_id && $user->buildings()->where('buildings.id', $lead->building_id)->exists();
            if (!$isAssigned && !$isBuildingAssigned) {
                throw new \App\Exceptions\BusinessException('Bạn không có quyền truy cập hoặc ghi nhật ký chăm sóc trên Lead này.', 403);
            }
        }
    }
}
