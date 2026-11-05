<?php

namespace App\Enums;

enum DebitNoteStatus: string
{
    case DRAFT = 'DRAFT';
    case ISSUED = 'ISSUED';
    case PARTIALLY_PAID = 'PARTIALLY_PAID';
    case PAID = 'PAID';
    case OVERDUE = 'OVERDUE';
    case DISPUTED = 'DISPUTED';
    case CANCELLED = 'CANCELLED';

    /**
     * Tên hiển thị tiếng Việt trên giao diện.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Dự thảo',
            self::ISSUED => 'Chờ thanh toán',
            self::PARTIALLY_PAID => 'Trả một phần',
            self::PAID => 'Đã thanh toán',
            self::OVERDUE => 'Quá hạn',
            self::DISPUTED => 'Khiếu nại',
            self::CANCELLED => 'Đã hủy',
        };
    }

    /**
     * Màu sắc Badge giao diện (Tailwind CSS).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800 border-gray-200',
            self::ISSUED => 'bg-blue-100 text-blue-800 border-blue-200',
            self::PARTIALLY_PAID => 'bg-amber-100 text-amber-800 border-amber-200',
            self::PAID => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::OVERDUE => 'bg-rose-100 text-rose-800 border-rose-200',
            self::DISPUTED => 'bg-purple-100 text-purple-800 border-purple-200',
            self::CANCELLED => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    /**
     * Mô tả chi tiết tiến trình thanh toán và công nợ.
     */
    public function description(): string
    {
        return match ($this) {
            self::DRAFT => 'Hóa đơn cước đang được kế toán tổng hợp, cho phép chỉnh sửa số liệu và các dòng chi tiết phí.',
            self::ISSUED => 'Hóa đơn đã phát hành chính thức, sinh mã VietQR và kích hoạt khóa bất biến (is_immutable = TRUE; cấm sửa/xóa).',
            self::PARTIALLY_PAID => 'Khách thuê đã thanh toán một phần tiền cước, vẫn còn số dư nợ tồn đọng cần thanh toán tiếp.',
            self::PAID => 'Khách thuê đã thanh toán đủ 100% tổng giá trị hóa đơn cước.',
            self::OVERDUE => 'Hóa đơn vượt quá hạn cam kết (due_date) mà chưa thu đủ, tự động tính lãi phạt chậm nộp.',
            self::DISPUTED => 'Khách thuê khiếu nại về chỉ số điện/nước hoặc đơn giá, tạm hoãn quy trình tính phạt chậm nộp.',
            self::CANCELLED => 'Hóa đơn nháp bị hủy bỏ (Lưu ý: Hóa đơn đã phát hành không được hủy mà phải lập phiếu điều chỉnh ADJUSTMENT).',
        };
    }

    /**
     * Kiểm tra hóa đơn có được phép sửa đổi số tiền hoặc các dòng chi tiết hay không.
     * Chỉ hóa đơn DRAFT mới được phép sửa hoặc xóa.
     */
    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Kiểm tra hóa đơn có ở trạng thái mở cho khách thanh toán hay không.
     */
    public function isPayable(): bool
    {
        return in_array($this, [self::ISSUED, self::PARTIALLY_PAID, self::OVERDUE]);
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
