<?php

namespace App\Exceptions;

use Exception;

/**
 * Class BusinessException
 *
 * Ngoại lệ dành riêng cho Tầng Nghiệp Vụ (Service Layer / BLL).
 * Cho phép ném các lỗi vi phạm quy tắc nghiệp vụ (ví dụ: trùng lịch thuê, số tiền không hợp lệ,
 * trạng thái hợp đồng không được phép chuyển đổi...) kèm HTTP Status Code và dữ liệu chi tiết.
 *
 * @package App\Exceptions
 */
class BusinessException extends Exception
{
    /**
     * Mã trạng thái HTTP (mặc định 422 - Unprocessable Entity hoặc 400 - Bad Request).
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * Dữ liệu lỗi bổ sung nếu có.
     *
     * @var mixed
     */
    protected mixed $errors;

    /**
     * Khởi tạo BusinessException.
     *
     * @param string $message Thông điệp lỗi hiển thị cho người dùng / client.
     * @param int $statusCode Mã trạng thái HTTP tương ứng (mặc định 422).
     * @param mixed $errors Dữ liệu lỗi chi tiết (mảng hoặc object).
     * @param \Throwable|null $previous Ngoại lệ gốc trước đó nếu có.
     */
    public function __construct(
        string $message = 'Thao tác không hợp lệ theo quy tắc nghiệp vụ.',
        int $statusCode = 422,
        mixed $errors = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Lấy mã trạng thái HTTP.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Lấy dữ liệu lỗi chi tiết.
     *
     * @return mixed
     */
    public function getErrors(): mixed
    {
        return $this->errors;
    }
}
