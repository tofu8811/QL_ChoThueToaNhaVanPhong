<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

/**
 * Service tầng nghiệp vụ quản lý tài khoản Quản trị viên (Admins).
 * Thuộc Module 5: Nhân sự & RBAC (Endpoints 72 - 76).
 */
class AdminUserService
{
    /**
     * Liệt kê danh sách các tài khoản Quản trị viên toàn hệ thống có phân trang và tìm kiếm.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdminList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Admin::query();

        // 1. Tìm kiếm theo từ khóa (Họ tên, email, số điện thoại)
        $keyword = $filters['keyword'] ?? $filters['q'] ?? null;
        if (!empty($keyword)) {
            $term = '%' . trim($keyword) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }

        // 2. Lọc theo trạng thái tài khoản (ACTIVE, LOCKED, PENDING)
        if (!empty($filters['status'])) {
            $query->where('status', strtoupper(trim($filters['status'])));
        }

        // 3. Lọc theo quyền Super Admin
        if (isset($filters['is_super_admin'])) {
            $isSuper = filter_var($filters['is_super_admin'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($isSuper)) {
                $query->where('is_super_admin', $isSuper);
            }
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Tạo tài khoản Quản trị viên mới.
     *
     * @param array $data
     * @return Admin
     */
    public function createAdmin(array $data): Admin
    {
        return Admin::create([
            'full_name'      => $data['full_name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'password'       => Hash::make($data['password']),
            'is_super_admin' => (bool) ($data['is_super_admin'] ?? false),
            'status'         => $data['status'] ?? 'ACTIVE',
        ]);
    }

    /**
     * Xem thông tin chi tiết một tài khoản Quản trị viên theo UUID v7.
     *
     * @param string $id
     * @return Admin
     */
    public function getAdminById(string $id): Admin
    {
        return Admin::findOrFail($id);
    }

    /**
     * Cập nhật thông tin tài khoản Quản trị viên.
     *
     * @param string $id
     * @param array $data
     * @return Admin
     */
    public function updateAdmin(string $id, array $data): Admin
    {
        $admin = Admin::findOrFail($id);

        $updateData = [];

        if (array_key_exists('full_name', $data)) {
            $updateData['full_name'] = $data['full_name'];
        }
        if (array_key_exists('email', $data)) {
            $updateData['email'] = $data['email'];
        }
        if (array_key_exists('phone', $data)) {
            $updateData['phone'] = $data['phone'];
        }
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        if (array_key_exists('is_super_admin', $data)) {
            $updateData['is_super_admin'] = (bool) $data['is_super_admin'];
        }
        if (array_key_exists('status', $data)) {
            $updateData['status'] = $data['status'];
        }

        $admin->update($updateData);

        return $admin;
    }

    /**
     * Xóa mềm (Soft Delete) tài khoản Quản trị viên.
     * Cấm tự xóa tài khoản của chính mình.
     *
     * @param string $id
     * @param Admin $currentAdmin
     * @return bool
     * @throws BusinessException
     */
    public function deleteAdmin(string $id, Admin $currentAdmin): bool
    {
        if ($currentAdmin->id === $id) {
            throw new BusinessException('Không được phép tự xóa tài khoản quản trị viên của chính mình.', 422);
        }

        $admin = Admin::findOrFail($id);

        return (bool) $admin->delete();
    }
}
