<?php

namespace App\Notifications\PropertyManager;

use App\Models\PropertyManager;
use App\Notifications\Concerns\BaseStaffCredentialNotification;

/**
 * Notification gửi thông tin tài khoản hoặc cấp lại mật khẩu cho Ban Quản Lý & Kỹ Thuật (PM).
 */
class PropertyManagerCredentialNotification extends BaseStaffCredentialNotification
{
    /**
     * Khởi tạo thông báo cho PM.
     *
     * @param string $plainPassword
     * @param PropertyManager $pm
     * @param array<int, string> $assignedBuildings
     * @param bool $isReset
     */
    public function __construct(
        string $plainPassword,
        PropertyManager $pm,
        array $assignedBuildings = [],
        bool $isReset = false
    ) {
        $loginUrl = route('pm.dashboard');

        $deptLabel = match (strtoupper((string) $pm->department)) {
            'TECHNICAL' => 'Kỹ thuật & Bảo trì',
            'SECURITY' => 'An ninh & Bảo vệ',
            'CUSTOMER_CARE' => 'Chăm sóc khách hàng & Dịch vụ',
            'CLEANING' => 'Vệ sinh & Môi trường',
            default => $pm->department ?: 'Ban Quản Lý Vận Hành',
        };

        parent::__construct(
            plainPassword: $plainPassword,
            roleName: 'Ban Quản Lý & Kỹ Thuật Tòa Nhà (PM)',
            employeeCode: $pm->employee_code,
            assignedBuildings: $assignedBuildings,
            loginUrl: $loginUrl,
            isReset: $isReset,
            department: $deptLabel
        );
    }
}
