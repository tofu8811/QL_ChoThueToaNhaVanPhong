<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuidV7;

    protected $table = 'payment_transactions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transaction_code', // Mã giao dịch duy nhất (VD: TXN-202611-0012)
        'building_id', // Tòa nhà nhận tiền
        'tenant_id', // Khách thuê chuyển tiền
        'debit_note_id', // Hóa đơn cước được gạch nợ
        'payment_method', // Phương thức: BANK_TRANSFER | VIETQR | CASH | CREDIT_CARD
        'amount_paid', // Số tiền khách thực nộp / chuyển khoản (VND)
        'bank_reference_number', // Mã bút toán / tham chiếu từ ngân hàng
        'proof_file_private_path', // Ảnh chụp ủy nhiệm chi / biên lai chuyển khoản
        'receipt_pdf_private_path', // File PDF phiếu thu tiền chính thức
        'paid_at', // Thời gian chuyển tiền
        'verification_status', // PENDING | APPROVED | REJECTED
        'verified_by_accountant_id', // Kế toán xác thực
        'verified_at', // Thời điểm xác thực
        'accountant_note', // Ghi chú đối soát của kế toán
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_paid' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
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

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class, 'debit_note_id');
    }

    public function verifiedByAccountant(): BelongsTo
    {
        return $this->belongsTo(Accountant::class, 'verified_by_accountant_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('verification_status', 'APPROVED');
    }
}
