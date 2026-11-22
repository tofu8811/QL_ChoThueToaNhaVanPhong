<?php

namespace App\Http\Middleware;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureWebRole: Phân quyền truy cập các phân hệ Web SSR (Role-Based Web Guard).
 * Tự động phân giải User từ Session hoặc Sanctum Cookie, hỗ trợ chuyển hướng thông minh.
 */
class EnsureWebRole
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Danh sách các vai trò được phép (admin, pm, sales, accountant, tenant)
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $this->resolveWebUser($request);

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Yêu cầu đăng nhập.'], 401);
            }
            return redirect('/?auth=required');
        }

        // Super Admin có toàn quyền truy cập trên mọi phân hệ để kiểm tra
        if ($user instanceof Admin && $user->is_super_admin) {
            $request->attributes->set('current_web_user', $user);
            $request->attributes->set('current_web_role', 'admin');
            return $next($request);
        }

        $userRole = $this->resolveRoleFromModel($user);

        // Chuẩn hóa danh sách vai trò cho phép
        $normalizedAllowed = array_map(function ($r) {
            $r = strtolower(trim($r));
            if ($r === 'property_manager') return 'pm';
            if ($r === 'sale') return 'sales';
            return $r;
        }, $roles);

        if (!$userRole || !in_array($userRole, $normalizedAllowed, true)) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Không có quyền truy cập.'], 403);
            }

            // Chuyển hướng thông minh về đúng phân hệ của user
            return $this->redirectToAuthorizedPortal($userRole)
                ->with('warning', 'Bạn không có thẩm quyền truy cập phân hệ vừa chọn.');
        }

        $request->attributes->set('current_web_user', $user);
        $request->attributes->set('current_web_role', $userRole);

        return $next($request);
    }

    /**
     * Phân giải người dùng từ session hoặc Cookie auth_token.
     */
    protected function resolveWebUser(Request $request): mixed
    {
        // 1. Kiểm tra các Session Guard
        foreach (['web', 'admin', 'property_manager', 'pm', 'sales', 'accountant', 'tenant'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                Auth::setUser($user);
                return $user;
            }
        }

        // 2. Kiểm tra Sanctum Bearer Token hoặc Cookie auth_token (hỗ trợ cả Cookie giải mã, thô & đã mã hóa)
        $rawToken = $request->bearerToken() ?? $request->cookie('auth_token') ?? $_COOKIE['auth_token'] ?? null;
        if ($rawToken) {
            $token = $rawToken;
            try {
                $decrypted = decrypt($rawToken, false);
                $validated = \Illuminate\Cookie\CookieValuePrefix::validate('auth_token', $decrypted, [app('encrypter')->getKey()]);
                $token = $validated ?? \Illuminate\Cookie\CookieValuePrefix::remove($decrypted);
            } catch (\Throwable $e) {
                $token = $rawToken;
            }
            $token = urldecode(trim((string) $token, "\"' "));
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                $user = $accessToken->tokenable;
                $guard = match (true) {
                    $user instanceof Admin => 'admin',
                    $user instanceof PropertyManager => 'property_manager',
                    $user instanceof Sale => 'sales',
                    $user instanceof Accountant => 'accountant',
                    $user instanceof Tenant => 'tenant',
                    default => 'web',
                };
                Auth::guard($guard)->setUser($user);
                Auth::setUser($user);

                // Đồng bộ đăng nhập vào Session nếu có Session để tối ưu truy vấn kế tiếp
                if ($request->hasSession()) {
                    try {
                        Auth::guard($guard)->login($user);
                    } catch (\Throwable $e) {}
                }

                return $user;
            }
        }

        return null;
    }

    /**
     * Xác định vai trò chuẩn dạng chuỗi từ Eloquent Model.
     */
    protected function resolveRoleFromModel(mixed $user): ?string
    {
        return match (true) {
            $user instanceof Admin => 'admin',
            $user instanceof PropertyManager => 'pm',
            $user instanceof Sale => 'sales',
            $user instanceof Accountant => 'accountant',
            $user instanceof Tenant => 'tenant',
            default => null,
        };
    }

    /**
     * Chuyển hướng người dùng về đúng phân hệ được phép.
     */
    protected function redirectToAuthorizedPortal(string $role): Response
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'pm' => redirect()->route('pm.dashboard'),
            'sales' => redirect()->route('sales.dashboard'),
            'accountant' => redirect()->route('accountant.dashboard'),
            'tenant' => redirect()->route('tenant-portal.page'),
            default => redirect()->route('home'),
        };
    }
}
