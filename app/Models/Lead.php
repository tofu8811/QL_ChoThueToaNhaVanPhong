<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, HasUuidV7, SoftDeletes;

    protected $table = 'leads';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contact_full_name', // Họ và tên người liên hệ đại diện doanh nghiệp
        'company_name', // Tên công ty có nhu cầu thuê mặt bằng
        'phone', // Số điện thoại liên hệ
        'email', // Email trao đổi thông tin báo giá
        'lead_source', // Nguồn: WEBSITE | HOTLINE | DIRECT_WALK_IN | BROKER | ADS
        'building_id', // Tòa nhà khách quan tâm
        'unit_id', // Mặt bằng cụ thể khách muốn thuê
        'desired_area_sqm', // Diện tích mong muốn thuê (m2)
        'budget_min', // Ngân sách tối thiểu dự kiến (VND)
        'budget_max', // Ngân sách tối đa dự kiến (VND)
        'preferred_site_tour_at', // Thời gian mong muốn xem mặt bằng thực tế
        'customer_note', // Ghi chú yêu cầu đặc biệt
        'assigned_sales_id', // Sales được phân công chăm sóc
        'assigned_at', // Thời điểm phân công cho Sales
        'sla_deadline_at', // Hạn chót Sales phải liên hệ lần đầu (SLA)
        'first_contacted_at', // Thời điểm Sales thực sự gọi/gặp lần đầu
        'status', // Trạng thái phễu: Cast to LeadStatus::class
        'lost_reason_category', // Danh mục lý do từ chối thuê
        'lost_reason_detail', // Chi tiết lý do thất bại
        'converted_tenant_id', // ID khách thuê sau khi ký hợp đồng thành công
        'converted_at', // Thời điểm ký hợp đồng thành công
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'desired_area_sqm' => 'decimal:2',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'preferred_site_tour_at' => 'datetime',
            'assigned_at' => 'datetime',
            'sla_deadline_at' => 'datetime',
            'first_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
            'status' => LeadStatus::class,
        ];
    }

    /**
     * Tòa nhà khách hàng đang quan tâm.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    /**
     * Mặt bằng cụ thể mà khách hàng muốn thuê.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Chuyên viên kinh doanh phụ trách chăm sóc.
     */
    public function assignedSales(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'assigned_sales_id');
    }

    /**
     * Doanh nghiệp khách thuê chính thức sau khi chốt thành công.
     */
    public function convertedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'converted_tenant_id');
    }

    /**
     * Lịch sử các hoạt động tương tác, gọi điện, dẫn đi xem thực tế.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'lead_id')->orderBy('event_at', 'desc');
    }

    /**
     * Scope lọc theo trạng thái Lead.
     */
    public function scopeStatus(Builder $query, LeadStatus $status): Builder
    {
        return $query->where('status', $status);
    }
}
