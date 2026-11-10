<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ForceJsonResponseMiddleware
 *
 * Middleware đảm bảo mọi Request gửi đến API luôn có Header 'Accept: application/json'.
 * Giúp triệt tiêu hoàn toàn hiện tượng Laravel redirect về trang login HTML khi chưa xác thực,
 * hoặc render trang HTML lỗi khi xảy ra ngoại lệ.
 *
 * @package App\Http\Middleware
 */
class ForceJsonResponseMiddleware
{
    /**
     * Xử lý request đến.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
