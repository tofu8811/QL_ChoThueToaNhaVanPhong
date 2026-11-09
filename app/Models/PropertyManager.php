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

class PropertyManager extends Authenticatable
{
    use HasFactory, HasUuidV7, Notifiable, SoftDeletes;

    protected $table = 'property_managers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_code', // Mã nhân viên BQL (VD: NV-BQL-001)
        'full_name', // Họ và tên
        'email', // Email đăng nhập
        'phone', // Số điện thoại
        'password', // Mật khẩu băm
        'department', // Bộ phận: TECHNICAL (Kỹ thuật) | CUSTOMER_CARE (Chăm sóc KH) | SECURITY (An ninh)
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
     * Các tòa nhà do nhân sự BQL này quản lý.
     */
    public function buildings(): BelongsToMany
    {
        return $this->belongsToMany(Building::class, 'manager_buildings', 'pm_id', 'building_id')
            ->withPivot(['is_primary', 'assigned_by_admin_id', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Các vé sự cố kỹ thuật mà PM này được phân công xử lý.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_pm_id');
    }
}
