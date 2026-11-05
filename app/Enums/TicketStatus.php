<?php

namespace App\Enums;

enum TicketStatus: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case ON_HOLD = 'ON_HOLD';
    case WAITING_CONFIRMATION = 'WAITING_CONFIRMATION';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Mới tiếp nhận',
            self::IN_PROGRESS => 'Đang xử lý',
            self::ON_HOLD => 'Tạm hoãn',
            self::WAITING_CONFIRMATION => 'Chờ nghiệm thu',
            self::COMPLETED => 'Hoàn tất',
            self::CANCELLED => 'Đã hủy',
        };
    }

    /**
     * Màu sắc Badge giao diện (Tailwind CSS).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::NEW => 'bg-blue-100 text-blue-800 border-blue-200',
            self::IN_PROGRESS => 'bg-amber-100 text-amber-800 border-amber-200',
            self::ON_HOLD => 'bg-purple-100 text-purple-800 border-purple-200',
            self::WAITING_CONFIRMATION => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::CANCELLED => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }

    /**
     * Mô tả chi tiết tiến trình hỗ trợ kỹ thuật và bảo trì.
     */
    public function description(): string
    {
        return match ($this) {
            self::NEW => 'Phiếu sự cố mới được khách thuê gửi từ Tenant Portal, đang chờ BQL phân công kỹ thuật viên.',
            self::IN_PROGRESS => 'Kỹ thuật viên đã tiếp nhận và đang tiến hành kiểm tra, sửa chữa tại hiện trường.',
            self::ON_HOLD => 'Tạm hoãn do chờ vật tư phụ tùng thay thế hoặc chờ nhà thầu bảo hành chuyên dụng từ bên ngoài.',
            self::WAITING_CONFIRMATION => 'Kỹ thuật viên đã sửa xong và tải ảnh bằng chứng (Proof of Work), chờ khách thuê bấm nghiệm thu trên Portal.',
            self::COMPLETED => 'Khách thuê đã xác nhận nghiệm thu hài lòng (hoặc hệ thống tự động đóng sau 72 giờ theo SLA).',
            self::CANCELLED => 'Phiếu yêu cầu bị hủy do gửi trùng lặp, báo nhầm hoặc khách thuê chủ động rút yêu cầu.',
        };
    }

    /**
     * Kiểm tra phiếu sự cố đã đóng hay còn đang mở.
     */
    public function isOpen(): bool
    {
        return !in_array($this, [self::COMPLETED, self::CANCELLED]);
    }

    /**
     * Kiểm tra có được phép tải ảnh minh chứng xử lý hoàn tất hay không.
     */
    public function canSubmitCompletion(): bool
    {
        return in_array($this, [self::IN_PROGRESS, self::ON_HOLD]);
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
