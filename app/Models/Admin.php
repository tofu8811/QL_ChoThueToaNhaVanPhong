<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, HasUuidV7, Notifiable, SoftDeletes;

    protected $table = 'admins';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'full_name', // Họ và tên đầy đủ của quản trị viên
        'email', // Email đăng nhập hệ thống quản trị
        'phone', // Số điện thoại liên hệ cá nhân
        'password', // Mật khẩu băm
        'is_super_admin', // True: Quản trị viên tối cao; False: Quản trị viên thường
        'status', // Trạng thái: ACTIVE | LOCKED | PENDING
        'last_login_at', // Thời điểm đăng nhập gần nhất
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
            'is_super_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Các tòa nhà do admin này tạo thông tin.
     */
    public function createdBuildings(): HasMany
    {
        return $this->hasMany(Building::class, 'created_by_admin_id');
    }
}
