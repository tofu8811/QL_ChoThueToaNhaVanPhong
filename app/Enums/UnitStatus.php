<?php

namespace App\Enums;

enum UnitStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case EXPIRING_SOON = 'EXPIRING_SOON';
    case LEASED = 'LEASED';
    case MAINTENANCE = 'MAINTENANCE';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Đang trống',
            self::EXPIRING_SOON => 'Sắp trống',
            self::LEASED => 'Đang cho thuê',
            self::MAINTENANCE => 'Đang sửa chữa',
        };
    }

    /**
     * Màu sắc Badge giao diện (Tailwind CSS).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::AVAILABLE => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::EXPIRING_SOON => 'bg-amber-100 text-amber-800 border-amber-200',
            self::LEASED => 'bg-blue-100 text-blue-800 border-blue-200',
            self::MAINTENANCE => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }

    /**
     * Mô tả chi tiết ý nghĩa nghiệp vụ.
     */
    public function description(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Mặt bằng sẵn sàng bàn giao cho khách thuê mới. Được phép hiển thị công khai trên Public Portal.',
            self::EXPIRING_SOON => 'Hợp đồng thuê hiện tại còn dưới 30 - 60 ngày hiệu lực, cho phép Sales chào thuê và đàm phán trước.',
            self::LEASED => 'Mặt bằng đang có khách thuê sử dụng và hợp đồng thuê còn hiệu lực. Bị khóa không cho phép lập hợp đồng trùng.',
            self::MAINTENANCE => 'Mặt bằng đang được bảo trì, nâng cấp nội thất hoặc cải tạo kỹ thuật; tạm ngưng tiếp nhận khách thuê.',
        };
    }

    /**
     * Kiểm tra mặt bằng có thể đưa vào hợp đồng thuê mới hay không.
     */
    public function canBeLeased(): bool
    {
        return in_array($this, [self::AVAILABLE, self::EXPIRING_SOON]);
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
