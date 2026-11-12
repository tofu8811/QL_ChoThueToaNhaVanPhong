<?php

namespace App\Enums;

enum TicketCategory: string
{
    case MAINTENANCE = 'MAINTENANCE';
    case OVERTIME_CONSTRUCTION = 'OVERTIME_CONSTRUCTION';
    case SECURITY_CLEANING = 'SECURITY_CLEANING';
    case SERVICE_COMPLAINT = 'SERVICE_COMPLAINT';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::MAINTENANCE => 'Sửa chữa kỹ thuật & Bảo trì',
            self::OVERTIME_CONSTRUCTION => 'Đăng ký thi công ngoài giờ',
            self::SECURITY_CLEANING => 'Vệ sinh & An ninh',
            self::SERVICE_COMPLAINT => 'Khiếu nại chất lượng dịch vụ',
        };
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
