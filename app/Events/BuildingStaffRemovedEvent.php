<?php

namespace App\Events;

use App\Enums\UserRole;
use App\Models\Building;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BuildingStaffRemovedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Khởi tạo sự kiện khi nhân viên (Sales, PM, Accountant) bị gỡ khỏi danh sách phụ trách tòa nhà.
     */
    public function __construct(
        public Building $building,
        public string $staffId,
        public UserRole|string $role
    ) {}

    /**
     * Kênh phát sóng riêng cho nhân sự bị gỡ phân công.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;

        $channels = [
            new Channel("staff.{$this->staffId}"),
            new Channel("user.{$this->staffId}"),
        ];

        if ($roleValue === 'sales') {
            $channels[] = new Channel("sales.{$this->staffId}");
        } elseif ($roleValue === 'property_manager' || $roleValue === 'pm') {
            $channels[] = new Channel("pm.{$this->staffId}");
        } elseif ($roleValue === 'accountant') {
            $channels[] = new Channel("accountant.{$this->staffId}");
        }

        return $channels;
    }

    /**
     * Tên sự kiện phát sóng cho client.
     */
    public function broadcastAs(): string
    {
        return 'building.removed';
    }

    /**
     * Dữ liệu payload gửi kèm qua WebSocket Reverb.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'building_id' => $this->building->id,
            'building_name' => $this->building->name,
            'building_code' => $this->building->building_code,
            'staff_id' => $this->staffId,
            'role' => $this->role instanceof UserRole ? $this->role->value : (string) $this->role,
            'message' => "Bạn vừa được gỡ khỏi danh sách phụ trách tòa nhà {$this->building->name} ({$this->building->building_code}).",
            'removed_at' => now()->toIso8601String(),
        ];
    }
}
