<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadPendingAssignmentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Khởi tạo sự kiện khi Lead không tìm được Sales khả dụng và chuyển sang PENDING_ASSIGN.
     */
    public function __construct(public Lead $lead) {}

    /**
     * Kênh phát sóng thông báo cho Quản trị viên (Admin).
     * Tạm dùng Public Channel để tiện test & demo khi chưa có Auth Token Module 5.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    /**
     * Tên sự kiện phát sóng.
     */
    public function broadcastAs(): string
    {
        return 'lead.pending_assignment';
    }

    /**
     * Dữ liệu payload gửi kèm qua WebSocket Reverb.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->lead->id,
            'contact_full_name' => $this->lead->contact_full_name,
            'company_name' => $this->lead->company_name,
            'phone' => $this->lead->phone,
            'building_id' => $this->lead->building_id,
            'building_name' => $this->lead->building?->name,
            'created_at' => $this->lead->created_at?->toIso8601String(),
            'message' => "Khách hàng {$this->lead->contact_full_name} đang chờ phân bổ chuyên viên kinh doanh.",
        ];
    }
}
