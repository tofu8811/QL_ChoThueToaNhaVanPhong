<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\TicketHistory;
class Tenant extends Authenticatable
{
    use HasFactory, HasUuidV7, Notifiable, SoftDeletes;

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
