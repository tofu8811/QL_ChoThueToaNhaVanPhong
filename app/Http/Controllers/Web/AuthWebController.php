<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Controller điều hướng không gian làm việc (Workspace Router) và quản lý phiên đăng nhập Web SSR.
 */
class AuthWebController extends Controller
{
    /**
     * Điều hướng thông minh về đúng phân hệ làm việc sau khi đăng nhập.
     */
    public function redirectToWorkspace(Request $request): RedirectResponse
    {
        $user = $this->resolveCurrentUser($request);

        if (!$user) {
            return redirect('/?auth=required');
        }

        return match (true) {
            $user instanceof Admin => redirect()->route('admin.dashboard'),
            $user instanceof PropertyManager => redirect()->route('pm.dashboard'),
            $user instanceof Sale => redirect()->route('sales.dashboard'),
            $user instanceof Accountant => redirect()->route('accountant.dashboard'),
            $user instanceof Tenant => redirect()->route('tenant-portal.page', ['tenant_id' => $user->id]),
            default => redirect()->route('home'),
        };
    }

    // Đăng xuất an toàn trên toàn bộ các Guards và dọn sạch Session/Cookies
    public function logout(Request $request): mixed
    {
        $rawToken = $request->bearerToken() ?? $request->cookie('auth_token') ?? $_COOKIE['auth_token'] ?? $request->input('auth_token') ?? null;
        if ($rawToken) {
            $token = $this->extractPlainToken($rawToken);
            $accessToken = PersonalAccessToken::findToken($token);
            $accessToken?->delete();
        }

        foreach (['web', 'admin', 'property_manager', 'pm', 'sales', 'accountant', 'tenant'] as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    Auth::guard($guard)->logout();
                }
            } catch (\Throwable $e) {}
        }

        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {}

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Bạn đã đăng xuất khỏi hệ thống thành công.',
            ])->withoutCookie('auth_token')->withoutCookie('tenant_id');
        }

        return redirect('/')
            ->withCookie(cookie()->forget('auth_token'))
            ->withCookie(cookie()->forget('tenant_id'))
            ->with('info', 'Bạn đã đăng xuất khỏi hệ thống thành công.');
    }

    /**
     * Phân giải User hiện tại từ Session hoặc Cookie.
     */
    protected function resolveCurrentUser(Request $request): mixed
    {
        foreach (['web', 'admin', 'property_manager', 'pm', 'sales', 'accountant', 'tenant'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        $rawToken = $request->bearerToken() ?? $request->cookie('auth_token') ?? $_COOKIE['auth_token'] ?? null;
        if ($rawToken) {
            $token = $this->extractPlainToken($rawToken);
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
     * Trích xuất token thô từ chuỗi cookie (xử lý mã hóa và URL-encoded).
     */
    protected function extractPlainToken(mixed $rawToken): string
    {
        $token = (string) $rawToken;
        try {
            $decrypted = decrypt($rawToken, false);
            $validated = \Illuminate\Cookie\CookieValuePrefix::validate('auth_token', $decrypted, [app('encrypter')->getKey()]);
            $token = $validated ?? \Illuminate\Cookie\CookieValuePrefix::remove($decrypted);
        } catch (\Throwable $e) {}

        return urldecode(trim($token, "\"' "));
    }
}
