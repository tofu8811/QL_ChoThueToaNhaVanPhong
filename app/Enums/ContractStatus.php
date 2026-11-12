<?php

namespace App\Enums;

enum ContractStatus: string
{
    case DRAFT = 'DRAFT';
    case ACTIVE = 'ACTIVE';
    case EXPIRING_SOON = 'EXPIRING_SOON';
    case EXPIRED = 'EXPIRED';
    case TERMINATED = 'TERMINATED';
    case RESERVED = 'RESERVED';
    case RENEWED = 'RENEWED';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Dự thảo',
            self::ACTIVE => 'Đang hiệu lực',
            self::EXPIRING_SOON => 'Sắp hết hạn',
            self::EXPIRED => 'Đã hết hạn',
            self::TERMINATED => 'Đã thanh lý',
            self::RESERVED => 'Giữ chỗ',
            self::RENEWED => 'Đã gia hạn',
        };
    }

    /**
     * Màu sắc Badge giao diện (Tailwind CSS).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800 border-gray-200',
            self::ACTIVE => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::EXPIRING_SOON => 'bg-amber-100 text-amber-800 border-amber-200',
            self::EXPIRED => 'bg-slate-100 text-slate-800 border-slate-200',
            self::TERMINATED => 'bg-rose-100 text-rose-800 border-rose-200',
            self::RESERVED => 'bg-orange-100 text-orange-800 border-orange-200',
            self::RENEWED => 'bg-cyan-100 text-cyan-800 border-cyan-200',
        };
    }

    /**
     * Mô tả chi tiết vòng đời pháp lý hợp đồng.
     */
    public function description(): string
    {
        return match ($this) {
            self::DRAFT => 'Hợp đồng đang trong quá trình soạn thảo điều khoản, chưa ký kết hoặc chưa nhận đủ tiền đặt cọc.',
            self::ACTIVE => 'Hợp đồng đã ký kết, đã thu đủ cọc và mặt bằng đã bàn giao đưa vào vận hành; kích hoạt chu kỳ tính cước định kỳ.',
            self::EXPIRING_SOON => 'Hợp đồng còn dưới 30 ngày hiệu lực, hệ thống tự động kích hoạt tiến trình cảnh báo gia hạn cho Sales.',
            self::EXPIRED => 'Hợp đồng đã kết thúc thời hạn tự nhiên mà hai bên không tiến hành gia hạn tiếp.',
            self::TERMINATED => 'Hợp đồng đã hoàn tất thủ tục thanh lý, đối soát hoàn cọc hoặc bị chấm dứt trước hạn.',
            self::RESERVED => 'Hợp đồng đặt cọc giữ chỗ tạm thời (Booking/Reservation) trong thời gian chờ khách hoàn tất hồ sơ và ký hợp đồng chính thức.',
            self::RENEWED => 'Hợp đồng của chu kỳ cũ đã hoàn tất gia hạn và được chuyển tiếp sang hợp đồng mới qua parent_contract_id.',
        };
    }

    /**
     * Kiểm tra hợp đồng có đang trong thời gian hiệu lực pháp lý hay không.
     */
    public function isEffective(): bool
    {
        return in_array($this, [self::ACTIVE, self::EXPIRING_SOON]);
    }

    /**
     * Kiểm tra hợp đồng có đang ở trạng thái đặt cọc giữ chỗ (Booking) hay không.
     */
    public function isReserved(): bool
    {
        return $this === self::RESERVED;
    }

    /**
     * Kiểm tra hợp đồng đã được gia hạn sang chu kỳ mới hay chưa.
     */
    public function isRenewed(): bool
    {
        return $this === self::RENEWED;
    }

    /**
     * Kiểm tra hợp đồng có đủ điều kiện phát sinh hóa đơn cước hàng tháng.
     */
    public function canGenerateInvoice(): bool
    {
        return $this->isEffective();
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
