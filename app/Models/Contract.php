<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Ticket;
class Contract extends Model
{
    use HasFactory, HasUuidV7, SoftDeletes;

    protected $table = 'contracts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contract_number', // Số hiệu hợp đồng pháp lý (VD: HĐ-2026/01-TDA)
        'parent_contract_id', // Liên kết hợp đồng cha khi gia hạn chu kỳ mới
        'building_id', // Tòa nhà cho thuê
        'tenant_id', // Doanh nghiệp đứng tên thuê
        'created_by_sales_id', // Nhân viên Sales lập hợp đồng / giữ chỗ
        'signed_date', // Ngày hai bên chính thức ký kết
        'start_date', // Ngày bắt đầu tính tiền thuê và bàn giao
        'end_date', // Ngày kết thúc hiệu lực hợp đồng
        'total_leased_area_sqm', // Tổng diện tích thực tế thuê (m2)
        'deposit_amount', // Tiền đặt cọc giữ chỗ và bảo đảm (VND)
        'booking_deposit_amount', // Tiền đặt cọc giữ chỗ tạm thời (VND)
        'reservation_expires_at', // Hạn chót giữ chỗ tạm thời
        'payment_cycle_months', // Chu kỳ thanh toán (1, 3, 6, 12 tháng/lần)
        'payment_due_day', // Ngày đến hạn nộp tiền định kỳ (1-28 hàng tháng)
        'late_fee_interest_percent', // Tỷ lệ lãi suất phạt chậm nộp (%/ngày)
        'annual_price_escalation_percent', // Tỷ lệ tăng giá thuê theo năm (%/năm)
        'contract_pdf_private_path', // File PDF scan hợp đồng có mộc đỏ
        'handover_minutes_private_path', // Biên bản bàn giao hiện trạng
        'approved_by_admin_id', // Admin phê duyệt kích hoạt hợp đồng
        'approved_by_pm_id', // PM phụ trách tòa nhà phê duyệt kích hoạt hợp đồng
        'status', // Trạng thái: Cast to ContractStatus::class
        'expiring_notified_at', // Thời điểm gửi thông báo sắp hết hạn
        'terminated_at', // Ngày chấm dứt hợp đồng sớm trước hạn
        'terminated_by_admin_id', // Admin phê duyệt thanh lý hợp đồng
        'terminated_by_pm_id', // PM phụ trách tòa nhà phê duyệt thanh lý hợp đồng
        'refund_deposit_amount', // Số tiền cọc hoàn trả lại cho khách
        'penalty_deposit_amount', // Số tiền phạt khấu trừ cọc
        'termination_note', // Lý do thanh lý hoặc đền bù
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'terminated_at' => 'date',
            'reservation_expires_at' => 'datetime',
            'expiring_notified_at' => 'datetime',
            'total_leased_area_sqm' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'booking_deposit_amount' => 'decimal:2',
            'refund_deposit_amount' => 'decimal:2',
            'penalty_deposit_amount' => 'decimal:2',
            'payment_cycle_months' => 'integer',
            'payment_due_day' => 'integer',
            'late_fee_interest_percent' => 'decimal:2',
            'annual_price_escalation_percent' => 'decimal:2',
            'status' => ContractStatus::class,
        ];
    }

    /**
     * Tòa nhà cho thuê.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    /**
     * Doanh nghiệp khách thuê.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Danh sách mặt bằng thuê trong hợp đồng này.
     */
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'contract_units', 'contract_id', 'unit_id')
            ->using(ContractUnit::class)
            ->withPivot(['id', 'building_id', 'leased_area_sqm', 'agreed_rental_price_per_sqm', 'agreed_service_fee_per_sqm', 'monthly_base_rent', 'monthly_service_fee'])
            ->withTimestamps();
    }

    /**
     * Chi tiết các bản ghi phân bổ sàn thuê.
     */
    public function contractUnits(): HasMany
    {
        return $this->hasMany(ContractUnit::class, 'contract_id');
    }

    /**
     * Hình ảnh scan hồ sơ hợp đồng.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ContractImage::class, 'contract_id')->orderBy('display_order');
    }

    /**
     * Các thông báo cước / hóa đơn (Debit Notes) phát sinh từ hợp đồng.
     */
    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class, 'contract_id');
    }

    /**
     * Scope lọc các hợp đồng đang có hiệu lực hoạt động.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ContractStatus::ACTIVE);
    }

    /**
     * Scope lọc các hợp đồng sắp hết hạn trong N ngày.
     */
    public function scopeExpiringWithinDays(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', ContractStatus::ACTIVE)
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'contract_id');
    }

    /**
     * Hợp đồng gốc của chu kỳ trước (nếu đây là hợp đồng gia hạn).
     */
    public function parentContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'parent_contract_id');
    }

    /**
     * Danh sách hợp đồng gia hạn cho các chu kỳ tiếp theo.
     */
    public function renewedContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'parent_contract_id');
    }

    /**
     * Chuyên viên Sales lập hợp đồng hoặc hồ sơ giữ chỗ.
     */
    public function createdBySales(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'created_by_sales_id');
    }

    /**
     * Quản trị viên duyệt ký hợp đồng.
     */
    public function approvedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by_admin_id');
    }

    /**
     * Property Manager phụ trách tòa nhà duyệt ký hợp đồng.
     */
    public function approvedByPm(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'approved_by_pm_id');
    }

    /**
     * Quản trị viên phê duyệt nghiệm thu thanh lý hợp đồng.
     */
    public function terminatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'terminated_by_admin_id');
    }

    /**
     * Property Manager phụ trách tòa nhà phê duyệt nghiệm thu thanh lý hợp đồng.
     */
    public function terminatedByPm(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'terminated_by_pm_id');
    }

    /**
     * Accessor lấy thực thể người duyệt ký kích hoạt hợp đồng (Admin hoặc Property Manager).
     */
    public function getApproverAttribute(): Admin|PropertyManager|null
    {
        return $this->approvedByAdmin ?? $this->approvedByPm;
    }

    /**
     * Accessor lấy thực thể người duyệt nghiệm thu thanh lý hợp đồng (Admin hoặc Property Manager).
     */
    public function getTerminatorAttribute(): Admin|PropertyManager|null
    {
        return $this->terminatedByAdmin ?? $this->terminatedByPm;
    }

    /**
     * Scope lọc hợp đồng đang ở trạng thái đặt cọc giữ chỗ (Booking).
     */
    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', ContractStatus::RESERVED);
    }

    /**
     * Scope lọc các hợp đồng đang chiếm dụng lịch mặt bằng (Đang hiệu lực, sắp hết hạn, hoặc đang giữ chỗ).
     */
    public function scopeActiveOrReserved(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ContractStatus::ACTIVE,
            ContractStatus::EXPIRING_SOON,
            ContractStatus::RESERVED,
        ]);
    }
}
