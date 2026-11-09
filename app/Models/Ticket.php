<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, HasUuidV7, SoftDeletes;

    protected $table = 'tickets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_code', // Mã phiếu hỗ trợ (VD: TK-202611-0089)
        'building_id', // Tòa nhà xảy ra sự việc
        'tenant_id', // Khách thuê tạo yêu cầu
        'contract_id', // Hợp đồng thuê hiệu lực
        'unit_id', // Vị trí mặt bằng gặp sự cố
        'category', // MAINTENANCE | OVERTIME_CONSTRUCTION | SECURITY_CLEANING | SERVICE_COMPLAINT
        'priority', // LOW | NORMAL | HIGH | URGENT
        'title', // Tiêu đề ngắn gọn
        'description', // Mô tả chi tiết hiện tượng sự cố
        'assigned_pm_id', // Nhân viên BQL được giao phụ trách
        'status', // Trạng thái: Cast to TicketStatus::class
        'on_hold_reason', // Lý do tạm ngưng
        'expected_completion_at', // Thời gian cam kết khắc phục (SLA)
        'resolution_summary', // Tóm tắt biên bản nghiệm thu
        'resolved_at', // Thời điểm kỹ thuật viên xử lý xong
        'completed_at', // Thời điểm khách nghiệm thu và đóng phiếu
        'tenant_rating', // Đánh giá hài lòng (1-5 sao)
        'tenant_feedback', // Ý kiến đóng góp
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_completion_at' => 'datetime',
            'resolved_at' => 'datetime',
            'completed_at' => 'datetime',
            'tenant_rating' => 'integer',
            'status' => TicketStatus::class,
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function assignedPropertyManager(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'assigned_pm_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function proofImages(): HasMany
    {
        return $this->hasMany(TicketProofImage::class, 'ticket_id');
    }

    public function scopeStatus(Builder $query, TicketStatus $status): Builder
    {
        return $query->where('status', $status);
    }
}
