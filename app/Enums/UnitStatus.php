<?php

namespace App\Enums;

enum SpaceStatus: string
{
    case AVAILABLE = 'available'; // Trống
    case RESERVED  = 'reserved';  // Đang giữ chỗ
    case RENTED    = 'rented';    // Đã ký hợp đồng cho thuê

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Trống',
            self::RESERVED  => 'Đang giữ chỗ',
            self::RENTED    => 'Đã cho thuê',
        };
    }
}