<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadAssignedToSalesEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Khởi tạo sự kiện khi Lead được phân bổ thành công cho nhân viên Sales.
     */
    public function __construct(public Lead $lead) {}

    /**
     * Kênh phát sóng cho Sales được chỉ định.
     * Tạm dùng Public Channel để tiện test & demo khi chưa có Auth Token Module 5.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("sales.{$this->lead->assigned_sales_id}"),
        ];
    }

    /**
     * Tên sự kiện phát sóng.
     */
    public function broadcastAs(): string
    {
        return 'lead.assigned';
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
            'sla_deadline_at' => $this->lead->sla_deadline_at?->toIso8601String(),
            'message' => "Bạn vừa được phân công chăm sóc khách hàng {$this->lead->contact_full_name}.",
        ];
    }
}
