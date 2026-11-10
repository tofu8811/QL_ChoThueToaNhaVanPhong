<?php

namespace App\Exceptions;

/**
 * Class LeaseOverlapException
 *
 * Ngoại lệ nghiệp vụ phát sinh khi xếp lịch thuê mặt bằng bị trùng lặp thời gian
 * với một hợp đồng thuê khác đang có hiệu lực.
 * Thay thế cho Trigger: trg_contract_units_prevent_lease_overlap_insert/update.
 *
 * @package App\Exceptions
 */
class LeaseOverlapException extends BusinessException
{
    /**
     * Khởi tạo LeaseOverlapException với mã HTTP 422 (Unprocessable Content).
     *
     * @param string $message Thông báo chi tiết mặt bằng và hợp đồng bị xung đột.
     * @param mixed $errors Dữ liệu chi tiết về khoảng ngày xung đột.
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Mặt bằng đã có hợp đồng thuê khác trong khoảng thời gian này. Không thể xếp trùng lịch.',
        mixed $errors = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 422, $errors, $previous);
    }
}
