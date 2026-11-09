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

class Sale extends Authenticatable
{
    use HasFactory, HasUuidV7, Notifiable, SoftDeletes;

    protected $table = 'sales';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_code', // Mã nhân viên kinh doanh (VD: NV-SALE-001)
        'full_name', // Họ và tên
        'email', // Email đăng nhập
        'phone', // Số điện thoại
        'password', // Mật khẩu băm
        'commission_rate', // Tỷ lệ hoa hồng (%)
        'max_concurrent_leads', // Số lượng lead tối đa phụ trách đồng thời
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
            'commission_rate' => 'decimal:2',
            'max_concurrent_leads' => 'integer',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Admin tạo tài khoản Sales này.
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Các tòa nhà mà Sales này được phân công bán/cho thuê.
     */
    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'sale_buildings', 'sales_id', 'building_id')
            ->withPivot(['is_active', 'assigned_by_admin_id', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Các leads CRM mà Sales đang chăm sóc.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_sales_id');
    }

    /**
     * Các doanh nghiệp khách thuê do Sales chốt hợp đồng.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'created_by_sales_id');
    }
}
