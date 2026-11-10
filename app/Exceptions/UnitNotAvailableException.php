<?php

namespace App\Exceptions;

/**
 * Class UnitNotAvailableException
 *
 * Ngoại lệ phát sinh khi mặt bằng không ở trạng thái sẵn sàng cho thuê
 * (ví dụ: đang sửa chữa, đã bàn giao, hoặc bị khóa).
 *
 * @package App\Exceptions
 */
class UnitNotAvailableException extends BusinessException
{
    /**
     * Khởi tạo UnitNotAvailableException với mã HTTP 422.
     *
     * @param string $message
     * @param mixed $errors
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Mặt bằng hiện không ở trạng thái sẵn sàng để thực hiện giao dịch.',
        mixed $errors = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 422, $errors, $previous);
    }
}
