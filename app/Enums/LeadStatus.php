<?php

namespace App\Enums;

enum LeadStatus: string
{
    case PENDING_ASSIGN = 'PENDING_ASSIGN';
    case NEW = 'NEW';
    case NEGOTIATING = 'NEGOTIATING';
    case WON = 'WON';
    case LOST = 'LOST';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING_ASSIGN => 'Chờ phân bổ',
            self::NEW => 'Mới tiếp nhận',
            self::NEGOTIATING => 'Đang đàm phán',
            self::WON => 'Thành công (Chốt thuê)',
            self::LOST => 'Thất bại (Từ chối)',
        };
    }

    /**
     * Màu sắc Badge giao diện (Tailwind CSS).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING_ASSIGN => 'bg-slate-100 text-slate-800 border-slate-200',
            self::NEW => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::NEGOTIATING => 'bg-amber-100 text-amber-800 border-amber-200',
            self::WON => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::LOST => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }

    /**
     * Mô tả chi tiết tiến trình phễu CRM.
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING_ASSIGN => 'Yêu cầu tư vấn mới từ Public Portal, đang chờ giải thuật hoặc Admin phân bổ cho nhân viên Sales.',
            self::NEW => 'Sales đã nhận Lead, đang trong khung thời hạn cam kết SLA 2 giờ làm việc để gọi điện liên hệ lần đầu.',
            self::NEGOTIATING => 'Sales đang hẹn khảo sát thực tế (Site tour), gửi bảng báo giá và thương thảo điều khoản hợp đồng.',
            self::WON => 'Khách chốt thuê mặt bằng, ký hợp đồng và được chuyển đổi thành công sang hồ sơ Khách thuê chính thức (Tenant).',
            self::LOST => 'Khách từ chối thuê, không liên lạc được hoặc hủy nhu cầu (bắt buộc nhập lý do thất bại).',
        };
    }

    /**
     * Kiểm tra hồ sơ Lead đã kết thúc vòng đời phễu hay chưa.
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::WON, self::LOST]);
    }

    /**
     * Lấy mảng tất cả các giá trị string của Enum.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Mảng [value => label] phục vụ render Dropdown / Select.
     */
    public static function options(): array
    {
        return array_combine(
            self::values(),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
