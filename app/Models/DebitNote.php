<?php

namespace App\Models;

use App\Enums\DebitNoteStatus;
use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebitNote extends Model
{
    use HasFactory, HasUuidV7, SoftDeletes;

    protected $table = 'debitnotes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'debit_note_code', // Mã số giấy báo cước (VD: DN-202611-0001)
        'building_id', // Tòa nhà phát hành cước
        'tenant_id', // Doanh nghiệp khách thuê nhận thông báo cước
        'contract_id', // Hợp đồng thuê làm căn cứ tính cước
        'billing_year', // Năm tính cước (VD: 2026)
        'billing_month', // Tháng tính cước (1 - 12)
        'note_type', // Loại cước: REGULAR | DEPOSIT | SETTLEMENT | ADJUSTMENT
        'issue_date', // Ngày chính thức phát hành phiếu báo cước
        'due_date', // Hạn chót thanh toán theo quy định hợp đồng
        'sub_total_amount', // Tổng tiền trước thuế GTGT (VND)
        'vat_percent', // Thuế suất giá trị gia tăng (%)
        'previous_debt_amount', // Số dư nợ cũ chuyển sang (nếu có)
        'late_fee_amount', // Tiền phạt chậm thanh toán kỳ trước
        'total_amount', // Tổng số tiền phải thanh toán
        'paid_amount', // Số tiền khách đã thanh toán thực tế
        'vietqr_payload', // Chuỗi mã hóa QR chuẩn EMVCo VietQR
        'is_immutable', // True: Hóa đơn đã khóa bất biến
        'status', // Trạng thái: Cast to DebitNoteStatus::class
        'created_by_accountant_id', // Kế toán lập phiếu báo cước
        'issued_by_accountant_id', // Kế toán trưởng ký duyệt phát hành
        'issued_at', // Thời điểm phát hành
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_year' => 'integer',
            'billing_month' => 'integer',
            'issue_date' => 'date',
            'due_date' => 'date',
            'issued_at' => 'datetime',
            'sub_total_amount' => 'decimal:2',
            'vat_percent' => 'decimal:2',
            'previous_debt_amount' => 'decimal:2',
            'late_fee_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'is_immutable' => 'boolean',
            'status' => DebitNoteStatus::class,
        ];
    }

    /**
     * Số tiền còn nợ lại cần thanh toán tiếp.
     */
    public function getRemainingDebtAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
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

    public function createdByAccountant(): BelongsTo
    {
        return $this->belongsTo(Accountant::class, 'created_by_accountant_id');
    }

    public function issuedByAccountant(): BelongsTo
    {
        return $this->belongsTo(Accountant::class, 'issued_by_accountant_id');
    }

    /**
     * Chi tiết các dòng cước phí cấu thành phiếu này.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DebitNoteDetail::class, 'debit_note_id');
    }

    /**
     * Lịch sử các giao dịch thanh toán chuyển khoản / VietQR.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'debit_note_id');
    }

    /**
     * Scope lọc theo trạng thái hóa đơn cước.
     */
    public function scopeStatus(Builder $query, DebitNoteStatus $status): Builder
    {
        return $query->where('status', $status);
    }
}
