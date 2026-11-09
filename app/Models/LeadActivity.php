<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'lead_activities';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lead_id', // Khách tiềm năng được tương tác
        'sales_id', // Chuyên viên kinh doanh thực hiện
        'activity_type', // CALL | SITE_TOUR | EMAIL | MEETING | NOTE | QUOTATION_SENT
        'title', // Tiêu đề ngắn tóm tắt hoạt động
        'content', // Nội dung chi tiết buổi làm việc
        'event_at', // Thời gian diễn ra sự kiện/cuộc gọi
        'attachment_path', // File đính kèm
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Khách hàng tiềm năng liên quan.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /**
     * Chuyên viên Sales thực hiện hoạt động này.
     */
    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }
}
