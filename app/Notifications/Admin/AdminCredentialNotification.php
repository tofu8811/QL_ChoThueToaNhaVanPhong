<?php

namespace App\Notifications\Admin;

use App\Notifications\Concerns\BaseStaffCredentialNotification;

/**
 * Notification gửi thông tin tài khoản hoặc cấp lại mật khẩu cho Quản trị viên (Admin / Super Admin).
 */
class AdminCredentialNotification extends BaseStaffCredentialNotification
{
    /**
     * Khởi tạo thông báo cho Admin.
     *
     * @param string $plainPassword
     * @param bool $isSuperAdmin
     * @param bool $isReset
     */
    public function __construct(
        string $plainPassword,
        bool $isSuperAdmin = false,
        bool $isReset = false
    ) {
        $roleName = $isSuperAdmin ? 'Quản trị viên cấp cao (Super Admin)' : 'Quản trị viên hệ thống (Admin)';
        $loginUrl = route('admin.dashboard');

        parent::__construct(
            plainPassword: $plainPassword,
            roleName: $roleName,
            employeeCode: null,
            assignedBuildings: [],
            loginUrl: $loginUrl,
            isReset: $isReset
        );
    }
}
