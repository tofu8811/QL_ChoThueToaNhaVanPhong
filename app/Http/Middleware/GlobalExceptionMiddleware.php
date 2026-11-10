<?php

namespace App\Http\Middleware;

use App\Exceptions\BusinessException;
use App\Support\DatabaseErrorClassifier;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Class GlobalExceptionMiddleware
 *
 * Middleware bắt lỗi tập trung (Global Exception Handling) cho toàn bộ API của hệ thống.
 * Đáp ứng 100% yêu cầu Tuần 05:
 * "Mọi lỗi sinh ra (như lỗi kết nối DB, lỗi không tìm thấy ID) phải được tóm lại ở Middleware
 * và trả về Frontend dưới một định dạng JSON chuẩn mực đồng nhất."
 *
 * Mẫu phản hồi chuẩn: { "status": "error", "message": "...", "code": 404, "errors": null }
 *
 * @package App\Http\Middleware
 */
class GlobalExceptionMiddleware
{
    /**
     * Xử lý request qua middleware pipeline và bắt mọi ngoại lệ phát sinh.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // Bắt trường hợp controller trả về response có lỗi exception đính kèm
            if ($response instanceof JsonResponse && $response->exception instanceof Throwable) {
                return $this->transformExceptionToResponse($request, $response->exception);
            }

            return $response;
        } catch (Throwable $e) {
            return $this->transformExceptionToResponse($request, $e);
        }
    }

    /**
     * Chuyển đổi ngoại lệ thành JSON Response chuẩn mực Tuần 05.
     *
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse
     */
    public function transformExceptionToResponse(Request $request, Throwable $e): JsonResponse
    {
        $status = 500;
        $message = 'Đã xảy ra sự cố hệ thống nội bộ. Vui lòng liên hệ ban quản trị.';
        $errors = null;

        if ($e instanceof BusinessException) {
            $status = $e->getStatusCode();
            $message = $e->getMessage();
            $errors = $e->getErrors();
        } elseif ($e instanceof ModelNotFoundException) {
            $status = 404;
            $modelName = class_basename($e->getModel());
            $message = match ($modelName) {
                'Building' => 'Tòa nhà không tồn tại.',
                'Unit' => 'Mặt bằng không tồn tại.',
                'Contract' => 'Hợp đồng không tồn tại.',
                'Tenant' => 'Khách thuê không tồn tại.',
                'Lead' => 'Thông tin liên hệ/Lead không tồn tại.',
                'Ticket' => 'Yêu cầu hỗ trợ/Ticket không tồn tại.',
                'DebitNote' => 'Phiếu báo cước không tồn tại.',
                default => 'Dữ liệu không tồn tại hoặc đã bị xóa.',
            };
        } elseif ($e instanceof NotFoundHttpException) {
            $status = 404;
            $message = 'Đường dẫn API không tồn tại.';
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $status = 405;
            $message = 'Phương thức HTTP không được hỗ trợ cho tài nguyên này.';
        } elseif ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Dữ liệu gửi lên không hợp lệ.';
            $errors = $e->errors();
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
            $message = 'Chưa xác thực hoặc phiên làm việc đã hết hạn.';
        } elseif ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            $status = 403;
            $message = 'Bạn không có quyền thực hiện thao tác này.';
        } elseif ($e instanceof QueryException || $e instanceof PDOException) {
            $dbError = DatabaseErrorClassifier::classify($e);
            $status = $dbError['status'];
            $message = $dbError['message'];
        } elseif ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: 'Đã xảy ra lỗi HTTP.';
        }

        // Ghi log lỗi có đầy đủ ngữ cảnh (Context Logging) theo Coding Standards Rule #5
        $this->logExceptionWithContext($request, $e, $status);

        $payload = [
            'status'  => 'error',
            'message' => $message,
            'code'    => $status,
            'errors'  => $errors,
        ];

        // Nếu môi trường bật debug, đính kèm thông tin stack trace an toàn để debug
        if (config('app.debug', false)) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => collect($e->getTrace())->take(5)->map(fn($t) => [
                    'file'     => $t['file'] ?? 'internal',
                    'line'     => $t['line'] ?? 0,
                    'function' => $t['function'] ?? '',
                ])->all(),
            ];
        }

        return response()->json($payload, $status);
    }

    /**
     * Ghi log ngoại lệ với đầy đủ ngữ cảnh truy cập.
     *
     * @param Request $request
     * @param Throwable $e
     * @param int $status
     * @return void
     */
    protected function logExceptionWithContext(Request $request, Throwable $e, int $status): void
    {
        $context = [
            'url'         => $request->fullUrl(),
            'method'      => $request->method(),
            'ip'          => $request->ip(),
            'user_id'     => $request->user()?->id,
            'status_code' => $status,
            'error_class' => get_class($e),
            'message'     => $e->getMessage(),
        ];

        if ($status >= 500) {
            Log::error('API Server Error: '.$e->getMessage(), array_merge($context, [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]));
        } elseif ($status >= 400 && $status !== 404) {
            Log::warning('API Client Error: '.$e->getMessage(), $context);
        }
    }
}
