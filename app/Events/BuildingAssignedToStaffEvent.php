<?php

namespace App\Events;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Building;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BuildingAssignedToStaffEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Khởi tạo sự kiện khi nhân viên (Sales, PM, Accountant) được gán quản lý/phụ trách tòa nhà.
     */
    public function __construct(
        public Building $building,
        public string $staffId,
        public UserRole|string $role,
        public ?Admin $admin = null
    ) {}

    /**
     * Kênh phát sóng thông báo riêng cho nhân sự được phân công.
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

        // Hỗ trợ thêm kênh đặc thù theo vai trò
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
        return 'building.assigned';
    }

    /**
     * Dữ liệu payload gửi kèm qua WebSocket Reverb.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $roleValue = $this->role instanceof UserRole ? $this->role->value : (string) $this->role;
        $roleLabel = match ($roleValue) {
            'sales' => 'Kinh doanh (Sales)',
            'property_manager', 'pm' => 'Ban Quản Lý (PM)',
            'accountant' => 'Kế toán',
            default => 'Nhân sự phụ trách'
        };

        $assignedByName = $this->admin?->full_name ?? 'Ban Giám Đốc';

        return [
            'building_id' => $this->building->id,
            'building_name' => $this->building->name,
            'building_code' => $this->building->building_code,
            'staff_id' => $this->staffId,
            'role' => $roleValue,
            'role_label' => $roleLabel,
            'assigned_by' => $assignedByName,
            'message' => "Bạn vừa được phân công phụ trách tòa nhà {$this->building->name} ({$this->building->building_code}) với vai trò {$roleLabel}.",
            'assigned_at' => now()->toIso8601String(),
        ];
    }
}
