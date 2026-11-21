<?php

namespace App\Http\Middleware;

use App\Exceptions\BusinessException;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Kiểm tra quyền truy cập theo vai trò người dùng (Multi-guard Role RBAC).
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Danh sách các vai trò được phép (admin, pm, sales, accountant, tenant)
     * @return Response
     * @throws BusinessException
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            throw new BusinessException('Yêu cầu xác thực phiên đăng nhập (Unauthenticated).', 401);
        }

        // Super Admin có toàn quyền truy cập trên mọi endpoint
        if ($user instanceof Admin && $user->is_super_admin) {
            return $next($request);
        }

        $userRole = null;
        if ($user instanceof Admin) {
            $userRole = 'admin';
        } elseif ($user instanceof PropertyManager) {
            $userRole = 'pm';
        } elseif ($user instanceof Sale) {
            $userRole = 'sales';
        } elseif ($user instanceof Accountant) {
            $userRole = 'accountant';
        } elseif ($user instanceof Tenant) {
            $userRole = 'tenant';
        }

        // Chuẩn hóa danh sách vai trò cho phép
        $normalizedAllowed = array_map(function ($r) {
            $r = strtolower(trim($r));
            if ($r === 'property_manager') return 'pm';
            if ($r === 'sale') return 'sales';
            return $r;
        }, $roles);

        if (!$userRole || !in_array($userRole, $normalizedAllowed, true)) {
            throw new BusinessException('Bạn không có quyền truy cập tài nguyên này.', 403);
        }

        return $next($request);
    }
}
