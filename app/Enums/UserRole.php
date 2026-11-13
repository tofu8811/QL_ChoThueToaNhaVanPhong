<?php

namespace App\Enums;

/**
 * Enum UserRole
 * 
 * Định nghĩa danh mục vai trò người dùng trong hệ thống (5 tác nhân cốt lõi).
 */
enum UserRole: string
{
    case ADMIN            = 'admin';
    case SALES            = 'sales';
    case PROPERTY_MANAGER = 'property_manager';
    case ACCOUNTANT       = 'accountant';
    case TENANT           = 'tenant';

    /**
     * Nhãn hiển thị tiếng Việt của vai trò.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN            => 'Quản trị viên',
            self::SALES            => 'Nhân viên Kinh doanh / Môi giới',
            self::PROPERTY_MANAGER => 'Ban quản lý Tòa nhà',
            self::ACCOUNTANT       => 'Nhân viên Kế toán',
            self::TENANT           => 'Khách thuê Doanh nghiệp',
        };
    }

    /**
     * Lấy danh sách toàn bộ các giá trị chuỗi của enum.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
