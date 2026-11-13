<?php

namespace App\Http\Middleware;

use App\Exceptions\BusinessException;
use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Kiểm tra quyền Quản trị viên tối cao (Super Admin).
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws BusinessException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!($user instanceof Admin) || !$user->is_super_admin) {
            throw new BusinessException('Chỉ Quản trị viên tối cao (Super Admin) mới có quyền truy cập chức năng này.', 403);
        }

        return $next($request);
    }
}
