<?php

use App\Http\Middleware\ForceJsonResponseMiddleware;
use App\Http\Middleware\GlobalExceptionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Nhóm middleware áp dụng cho toàn bộ API routes (/api/*)
        $middleware->api(prepend: [
            ForceJsonResponseMiddleware::class,
            GlobalExceptionMiddleware::class,
        ]);

        $middleware->alias([
            'force.json'  => ForceJsonResponseMiddleware::class,
            'api.catch'   => GlobalExceptionMiddleware::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Luôn trả về JSON khi request vào endpoint API
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Tầng phòng thủ thứ 2 (Dual-layer defense):
        // Bắt trọn vẹn cả các ngoại lệ phát sinh ngoài Middleware pipeline (như router 404, 405)
        // và định dạng đồng nhất 100% theo chuẩn Tuần 05
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return app(GlobalExceptionMiddleware::class)->transformExceptionToResponse($request, $e);
            }
        });
    })->create();
