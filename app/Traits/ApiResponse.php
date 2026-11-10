<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Trait ApiResponse
 *
 * Cung cấp các phương thức chuẩn hóa định dạng phản hồi JSON cho toàn bộ tầng Controller/API.
 * Đảm bảo 100% tuân thủ cấu trúc response chuẩn theo đề cương Tuần 05:
 * - Phản hồi thành công: { "status": "success", "message": "...", "code": 200, "data": ... }
 * - Phản hồi lỗi: { "status": "error", "message": "...", "code": 404, "errors": ... }
 *
 * @package App\Traits
 */
trait ApiResponse
{
    /**
     * Trả về phản hồi thành công chuẩn hóa.
     *
     * @param mixed $data Dữ liệu trả về (object, array, primitive).
     * @param string $message Thông điệp thành công.
     * @param int $code Mã trạng thái HTTP (mặc định 200).
     * @param array $headers Headers bổ sung nếu có.
     * @return JsonResponse
     */
    public function successResponse(
        mixed $data = null,
        string $message = 'Thành công.',
        int $code = 200,
        array $headers = []
    ): JsonResponse {
        $response = [
            'status'  => 'success',
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ];

        return response()->json($response, $code, $headers);
    }

    /**
     * Trả về phản hồi lỗi chuẩn hóa theo mẫu Tuần 05.
     *
     * @param string $message Thông điệp lỗi hiển thị cho client.
     * @param int $code Mã trạng thái HTTP (400, 401, 403, 404, 422, 500...).
     * @param mixed $errors Mảng hoặc object chứa danh sách lỗi chi tiết (vd: validation errors).
     * @param array $headers Headers bổ sung nếu có.
     * @param mixed $debug Thông tin gỡ lỗi chỉ xuất hiện khi APP_DEBUG=true.
     * @return JsonResponse
     */
    public function errorResponse(
        string $message = 'Đã xảy ra lỗi.',
        int $code = 400,
        mixed $errors = null,
        array $headers = []
    ): JsonResponse {
        $response = [
            'status'  => 'error',
            'message' => $message,
            'code'    => $code,
            'errors'  => $errors,
        ];

        return response()->json($response, $code, $headers);
    }

    /**
     * Trả về phản hồi phân trang chuẩn hóa kèm metadata.
     *
     * @param LengthAwarePaginator $paginator Đối tượng phân trang từ Eloquent/Query Builder.
     * @param string $message Thông điệp thành công.
     * @param int $code Mã trạng thái HTTP (mặc định 200).
     * @return JsonResponse
     */
    public function paginateResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Lấy danh sách thành công.',
        int $code = 200
    ): JsonResponse {
        $response = [
            'status'  => 'success',
            'message' => $message,
            'code'    => $code,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ];

        return response()->json($response, $code);
    }
}
