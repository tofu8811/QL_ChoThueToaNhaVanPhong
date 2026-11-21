<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\TicketHistory;
class Tenant extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuidV7, Notifiable, SoftDeletes;

    protected $table = 'tenants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_code', // Mã định danh khách thuê (VD: TN-2026-001)
        'converted_from_lead_id', // Nguồn gốc lead chuyển đổi thành khách chính thức
        'company_name', // Tên đầy đủ của doanh nghiệp thuê mặt bằng
        'tax_code', // Mã số thuế doanh nghiệp
        'business_license_private_path', // File PDF Giấy phép kinh doanh
        'representative_name', // Họ tên người đại diện pháp luật
        'representative_position', // Chức vụ của người đại diện (Giám đốc,...)
        'headquarter_address', // Địa chỉ trụ sở chính theo ĐKKD
        'login_email', // Email đăng nhập Cổng thông tin khách thuê (Tenant Portal)
        'billing_email', // Email chính nhận thông báo cước và hóa đơn VAT
        'contact_phone', // Số điện thoại liên hệ khẩn cấp
        'password', // Mật khẩu băm cổng Portal
        'must_change_password', // Bắt buộc đổi mật khẩu lần đầu
        'password_changed_at', // Thời điểm đổi mật khẩu gần nhất
        'initial_password_issued_at', // Thời điểm cấp mật khẩu khởi tạo
        'status', // Trạng thái: ACTIVE | LOCKED | PENDING
        'last_login_at', // Thời điểm đăng nhập gần nhất
        'created_by_sales_id', // Sales chốt hợp đồng
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'initial_password_issued_at' => 'datetime',
        ];
    }

    /**
     * Xác định địa chỉ email nhận link khôi phục mật khẩu (Multi-guard Password Broker).
     */
    public function getEmailForPasswordReset(): string
    {
        return (string) ($this->login_email ?: $this->billing_email);
    }

    /**
     * Định tuyến địa chỉ email nhận thông báo từ hệ thống qua Notifiable.
     */
    public function routeNotificationForMail($notification = null): string
    {
        return (string) ($this->login_email ?: $this->billing_email);
    }

    /**
     * Accessor tạo thuộc tính email tương thích mặc định của Laravel.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->login_email ?: $this->billing_email;
    }

    /**
     * Gửi thông báo khôi phục mật khẩu kèm token có thời hạn.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\TenantResetPasswordNotification($token));
    }

    /**
     * Accessor tạo thuộc tính full_name tương thích toàn bộ hệ thống Auth & Portal.
     */
    public function getFullNameAttribute(): string
    {
        return $this->company_name ?: ($this->representative_name ?: 'Doanh nghiệp khách thuê');
    }

    /**
     * Lead CRM gốc trước khi chuyển đổi.
     */
    public function convertedFromLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'converted_from_lead_id');
    }

    /**
     * Chuyên viên Sales chốt ký hợp đồng khách này.
     */
    public function createdBySales(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'created_by_sales_id');
    }

    /**
     * Danh sách hợp đồng thuê của doanh nghiệp.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'tenant_id');
    }

    /**
     * Danh sách thông báo cước / hóa đơn (Debit Notes).
     */
    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class, 'tenant_id');
    }

    /**
     * Các phiếu yêu cầu hỗ trợ sự cố do khách gửi.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'tenant_id');
    }
    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'creator_tenant_id');
    }

    public function ticketHistories(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'tenant_actor_id');
    }

}
