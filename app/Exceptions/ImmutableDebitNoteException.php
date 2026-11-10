<?php

namespace App\Exceptions;

/**
 * Class ImmutableDebitNoteException
 *
 * Ngoại lệ bảo vệ tính bất biến của Giấy báo cước (Debit Note).
 * Phát sinh khi người dùng cố tình cập nhật hoặc xóa dòng chi tiết cước
 * của một hóa đơn đã được phát hành chính thức (is_immutable = true).
 * Thay thế cho Trigger: trg_debitnote_detail_prevent_update/delete.
 *
 * @package App\Exceptions
 */
class ImmutableDebitNoteException extends BusinessException
{
    /**
     * Khởi tạo ImmutableDebitNoteException với mã HTTP 422 (hoặc 403 Forbidden).
     *
     * @param string $message Thông báo lỗi vi phạm tính bất biến.
     * @param mixed $errors
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Giấy báo cước đã phát hành chính thức (Bất biến). Mọi thao tác sửa đổi hoặc xóa chi tiết đều bị nghiêm cấm.',
        mixed $errors = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 422, $errors, $previous);
    }
}
