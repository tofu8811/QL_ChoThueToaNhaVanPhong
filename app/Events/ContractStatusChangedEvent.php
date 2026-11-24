<?php

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ContractStatusChangedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Khởi tạo sự kiện khi hợp đồng thuê thay đổi trạng thái (Ký duyệt, Từ chối, Thanh lý, Gia hạn).
     *
     * @param Contract $contract
     * @param string $action 'SIGNED' | 'REJECTED' | 'TERMINATED' | 'RENEWED' | 'STATUS_CHANGED'
     * @param string|null $oldStatus
     * @param string|null $newStatus
     * @param mixed $actor Nhân sự thực hiện hành động (Admin, PM, Sale...)
     */
    public function __construct(
        public Contract $contract,
        public string $action,
        public ?string $oldStatus = null,
        public ?string $newStatus = null,
        public mixed $actor = null
    ) {}

    /**
     * Kênh phát sóng: Tự động gom toàn bộ các Sales, PMs, Accountants phụ trách tòa nhà của hợp đồng này + Admin.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel("admin-notifications"),
            new Channel("building.{$this->contract->building_id}.contracts"),
        ];

        // Lấy danh sách ID toàn bộ nhân viên phụ trách tòa nhà
        $bId = $this->contract->building_id;

        $staffIds = collect([$this->contract->created_by_sales_id])
            ->merge(
                DB::table('sale_buildings')
                    ->where('building_id', $bId)
                    ->where('is_active', true)
                    ->pluck('sales_id')
            )
            ->merge(
                DB::table('manager_buildings')
                    ->where('building_id', $bId)
                    ->pluck('pm_id')
            )
            ->merge(
                DB::table('account_buildings')
                    ->where('building_id', $bId)
                    ->pluck('accountant_id')
            )
            ->filter()
            ->unique();

        foreach ($staffIds as $staffId) {
            $channels[] = new Channel("staff.{$staffId}");
        }

        return $channels;
    }

    /**
     * Tên sự kiện phát sóng cho client.
     */
    public function broadcastAs(): string
    {
        return 'contract.status_changed';
    }

    /**
     * Dữ liệu payload gửi kèm qua WebSocket Reverb.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $buildingName = $this->contract->building?->name ?? 'Tòa nhà';
        $actorName = $this->actor?->full_name ?? 'Hệ thống';

        $actionDesc = match (strtoupper($this->action)) {
            'SIGNED' => 'đã được ký duyệt chính thức',
            'REJECTED' => 'đã bị từ chối / hủy bỏ',
            'TERMINATED' => 'đã được thanh lý và nghiệm thu',
            'RENEWED' => 'đã được gia hạn sang chu kỳ mới',
            default => "đã chuyển trạng thái sang {$this->newStatus}"
        };

        $message = "Hợp đồng {$this->contract->contract_code} tại {$buildingName} {$actionDesc} bởi {$actorName}.";

        return [
            'contract_id' => $this->contract->id,
            'contract_code' => $this->contract->contract_code,
            'building_id' => $this->contract->building_id,
            'building_name' => $buildingName,
            'action' => strtoupper($this->action),
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'actor_name' => $actorName,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
