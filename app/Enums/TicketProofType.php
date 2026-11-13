<?php

namespace App\Enums;

enum TicketProofType: string
{
    case INCIDENT = 'INCIDENT';
    case COMPLETION = 'COMPLETION';
    case LOG_ATTACHMENT = 'LOG_ATTACHMENT';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::INCIDENT => 'Hiện trạng sự cố (Before)',
            self::COMPLETION => 'Nghiệm thu kết quả sau sửa chữa (After)',
            self::LOG_ATTACHMENT => 'Tài liệu / Biên bản đính kèm',
        };
    }

    /**
     * Chuẩn hóa từ chuỗi đầu vào (hỗ trợ BEFORE, AFTER).
     */
    public static function normalize(string $type): string
    {
        $upper = strtoupper(trim($type));
        return match ($upper) {
            'BEFORE', 'INCIDENT' => self::INCIDENT->value,
            'AFTER', 'COMPLETION' => self::COMPLETION->value,
            'LOG_ATTACHMENT', 'LOG' => self::LOG_ATTACHMENT->value,
            default => self::INCIDENT->value,
        };
    }

    /**
     * Lấy mảng tất cả các giá trị string của Enum.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
