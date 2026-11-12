<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service tầng nghiệp vụ (BLL) phụ trách Quản lý Lịch sử Tương tác & Chăm sóc Lead (Lead Activities).
 */
class LeadActivityService
{
    /**
     * Lấy toàn bộ dòng thời gian (Timeline) các lần gọi, email, site tour của Lead.
     *
     * @param Lead $lead
     * @return Collection<int, LeadActivity>
     */
    public function getActivities(Lead $lead): Collection
    {
        return $lead->activities()
            ->with('sales')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ghi nhật ký tương tác mới và tự động cập nhật contact tracking.
     *
     * @param Lead $lead
     * @param array<string, mixed> $data
     * @param string|null $salesId
     * @return LeadActivity
     */
    public function logActivity(Lead $lead, array $data, ?string $salesId = null): LeadActivity
    {
        return DB::transaction(function () use ($lead, $data, $salesId) {
            $activity = LeadActivity::create([
                'lead_id' => $lead->id,
                'sales_id' => $salesId ?? $lead->assigned_sales_id,
                'activity_type' => $data['activity_type'],
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
                'event_at' => $data['event_at'] ?? now(),
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);

            // Tự động kích hoạt first_contacted_at nếu là lần tương tác đầu tiên
            if ($lead->first_contacted_at === null) {
                $lead->first_contacted_at = now();
            }

            // Nếu Lead đang ở trạng thái NEW, tự động chuyển sang NEGOTIATING
            if ($lead->status === LeadStatus::NEW) {
                $lead->status = LeadStatus::NEGOTIATING;
            }

            $lead->touch();
            $lead->save();

            return $activity->load('sales');
        });
    }
}
