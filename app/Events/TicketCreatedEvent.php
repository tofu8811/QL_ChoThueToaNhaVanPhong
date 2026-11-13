<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Khởi tạo sự kiện khi khách thuê gửi yêu cầu hỗ trợ mới.
     */
    public function __construct(public Ticket $ticket) {}

    /**
     * Kênh phát sóng thông báo đến Ban quản lý tòa nhà (Reverb WebSocket).
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("building.{$this->ticket->building_id}.pm"),
            new Channel("tickets"),
        ];
    }

    /**
     * Tên sự kiện phát sóng.
     */
    public function broadcastAs(): string
    {
        return 'ticket.created';
    }

    /**
     * Dữ liệu payload gửi kèm qua WebSocket Reverb.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ticket->id,
            'ticket_code' => $this->ticket->ticket_code,
            'title' => $this->ticket->title,
            'category' => $this->ticket->category,
            'priority' => $this->ticket->priority,
            'building_id' => $this->ticket->building_id,
            'building_name' => $this->ticket->building?->name,
            'unit_id' => $this->ticket->unit_id,
            'unit_code' => $this->ticket->unit?->unit_code,
            'creator_tenant_id' => $this->ticket->creator_tenant_id,
            'status' => $this->ticket->status instanceof \BackedEnum ? $this->ticket->status->value : $this->ticket->status,
            'created_at' => $this->ticket->created_at?->toIso8601String(),
            'message' => "Có yêu cầu hỗ trợ mới [{$this->ticket->ticket_code}] từ khách thuê tại tòa nhà {$this->ticket->building?->name}.",
        ];
    }
}
