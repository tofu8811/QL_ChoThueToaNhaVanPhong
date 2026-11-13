<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'LOW';
    case NORMAL = 'NORMAL';
    case HIGH = 'HIGH';
    case URGENT = 'URGENT';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Thấp',
            self::NORMAL => 'Bình thường',
            self::HIGH => 'Cao',
            self::URGENT => 'Khẩn cấp',
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
