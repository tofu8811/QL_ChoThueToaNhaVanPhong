<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

/**
 * Class DatabaseErrorClassifier
 *
 * Bộ phân loại ngoại lệ Cơ sở dữ liệu thông minh.
 * Phân tích các mã lỗi SQL/PDO của MySQL để chuyển đổi thành thông điệp tiếng Việt thân thiện
 * và mã HTTP phù hợp (409 Conflict, 422 Unprocessable, 503 Service Unavailable...),
 * đồng thời ngăn chặn hoàn toàn việc để lộ câu lệnh SQL và cấu trúc bảng nhạy cảm ra ngoài.
 *
 * @package App\Support
 */
class DatabaseErrorClassifier
{
    /**
     * Mã lỗi MySQL phổ biến.
     */
    public const MYSQL_DUPLICATE_ENTRY = 1062;
    public const MYSQL_FOREIGN_KEY_DELETE_RESTRICT = 1451;
    public const MYSQL_FOREIGN_KEY_ADD_RESTRICT = 1452;
    public const MYSQL_ACCESS_DENIED = 1045;
    public const MYSQL_CONNECTION_REFUSED = 2002;
    public const MYSQL_CANNOT_CONNECT = 2003;
    public const MYSQL_LOCK_WAIT_TIMEOUT = 1205;
    public const MYSQL_DEADLOCK = 1213;
    public const MYSQL_COLUMN_CANNOT_BE_NULL = 1048;

    /**
     * Phân loại lỗi CSDL từ QueryException hoặc PDOException.
     *
     * @param Throwable $exception Ngoại lệ cơ sở dữ liệu phát sinh.
     * @return array{status: int, message: string} Mảng gồm mã HTTP status và thông điệp lỗi tiếng Việt an toàn.
     */
    public static function classify(Throwable $exception): array
    {
        $errorCode = self::extractErrorCode($exception);

        return match ($errorCode) {
            self::MYSQL_DUPLICATE_ENTRY => [
                'status' => 409,
                'message' => 'Dữ liệu đã tồn tại trong hệ thống (Trùng lặp dữ liệu duy nhất).',
            ],
            self::MYSQL_FOREIGN_KEY_DELETE_RESTRICT => [
                'status' => 409,
                'message' => 'Không thể xóa hoặc thay đổi dữ liệu do đang có ràng buộc dữ liệu liên quan.',
            ],
            self::MYSQL_FOREIGN_KEY_ADD_RESTRICT => [
                'status' => 422,
                'message' => 'Dữ liệu tham chiếu không tồn tại trong hệ thống hoặc không hợp lệ.',
            ],
            self::MYSQL_COLUMN_CANNOT_BE_NULL => [
                'status' => 400,
                'message' => 'Thiếu dữ liệu bắt buộc của trường dữ liệu trong cơ sở dữ liệu.',
            ],
            self::MYSQL_LOCK_WAIT_TIMEOUT, self::MYSQL_DEADLOCK => [
                'status' => 409,
                'message' => 'Hệ thống đang bận xử lý giao dịch đồng thời. Vui lòng thử lại sau ít giây.',
            ],
            self::MYSQL_ACCESS_DENIED, self::MYSQL_CONNECTION_REFUSED, self::MYSQL_CANNOT_CONNECT => [
                'status' => 503,
                'message' => 'Không thể kết nối đến máy chủ cơ sở dữ liệu. Vui lòng liên hệ ban quản trị.',
            ],
            default => [
                'status' => 500,
                'message' => 'Đã xảy ra lỗi trong quá trình thực thi cơ sở dữ liệu.',
            ],
        };
    }

    /**
     * Trích xuất mã lỗi số nguyên từ QueryException hoặc PDOException.
     *
     * @param Throwable $exception
     * @return int|null
     */
    protected static function extractErrorCode(Throwable $exception): ?int
    {
        if ($exception instanceof QueryException && isset($exception->errorInfo[1])) {
            return (int) $exception->errorInfo[1];
        }

        if ($exception instanceof PDOException && isset($exception->errorInfo[1])) {
            return (int) $exception->errorInfo[1];
        }

        $code = $exception->getCode();
        if (is_numeric($code) && (int) $code > 0) {
            return (int) $code;
        }

        return null;
    }
}
