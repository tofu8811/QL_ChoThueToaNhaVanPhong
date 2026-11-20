<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Exceptions\BusinessException;
use App\Models\Admin;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureRole: Phân quyền truy cập dựa trên vai trò (RBAC).
 *
 * Kiểm tra xem người dùng hiện tại có thuộc một trong các vai trò được phép truy cập hay không.
 * Hỗ trợ các bí danh (alias) thông dụng: pm -> property_manager, sale -> sales.
 */
class EnsureRole
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Xử lý request kiểm tra vai trò.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Danh sách các vai trò được phép (ví dụ: 'admin', 'property_manager', 'sales', 'tenant')
     * @return Response
     * @throws BusinessException
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            throw new BusinessException('Yêu cầu xác thực tài khoản.', 401);
        }

        // Super Admin có quyền tối cao trên toàn bộ hệ thống
        if ($user instanceof Admin && $user->is_super_admin) {
            return $next($request);
        }

        $userRole = $this->authService->resolveRoleFromUser($user);

        // Chuẩn hóa danh sách vai trò cho phép và hỗ trợ bí danh (aliases)
        $normalizedRoles = [];
        foreach ($roles as $role) {
            $r = strtolower(trim($role));
            $normalized = match ($r) {
                'pm' => UserRole::PROPERTY_MANAGER->value,
                'sale' => UserRole::SALES->value,
                default => $r,
            };
            $normalizedRoles[] = $normalized;
        }

        if (!in_array($userRole, $normalizedRoles, true)) {
            throw new BusinessException('Tài khoản của bạn không có quyền thực hiện thao tác này.', 403);
        }

        return $next($request);
    }
}
