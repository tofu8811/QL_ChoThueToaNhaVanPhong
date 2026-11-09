<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Accountant extends Authenticatable
{
    use HasFactory, HasUuidV7, Notifiable, SoftDeletes;

    protected $table = 'accountants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_code', // Mã nhân viên kế toán (VD: NV-KT-001)
        'full_name', // Họ và tên
        'email', // Email đăng nhập
        'phone', // Số điện thoại
        'password', // Mật khẩu băm
        'status', // Trạng thái: ACTIVE | LOCKED | PENDING
        'last_login_at', // Thời điểm đăng nhập gần nhất
        'created_by_admin_id', // Admin tạo tài khoản
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Admin tạo tài khoản này.
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Các tòa nhà mà kế toán phụ trách đối soát công nợ.
     */
    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'account_buildings', 'accountant_id', 'building_id')
            ->withPivot(['assigned_by_admin_id', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Các hóa đơn cước (Debit Notes) do kế toán này lập.
     */
    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class, 'created_by_accountant_id');
    }

    /**
     * Các giao dịch VietQR đã được kế toán này đối soát và xác thực.
     */
    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'verified_by_accountant_id');
    }
}
