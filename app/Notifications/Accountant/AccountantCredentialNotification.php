<?php

namespace App\Notifications\Accountant;

use App\Models\Accountant;
use App\Notifications\Concerns\BaseStaffCredentialNotification;

/**
 * Notification gửi thông tin tài khoản hoặc cấp lại mật khẩu cho Kế toán công nợ tòa nhà (Accountant).
 */
class AccountantCredentialNotification extends BaseStaffCredentialNotification
{
    /**
     * Khởi tạo thông báo cho Kế toán.
     *
     * @param string $plainPassword
     * @param Accountant $accountant
     * @param array<int, string> $assignedBuildings
     * @param bool $isReset
     */
    public function __construct(
        string $plainPassword,
        Accountant $accountant,
        array $assignedBuildings = [],
        bool $isReset = false
    ) {
        $loginUrl = route('accountant.dashboard');

        parent::__construct(
            plainPassword: $plainPassword,
            roleName: 'Kế toán công nợ & Thu cước tòa nhà',
            employeeCode: $accountant->employee_code,
            assignedBuildings: $assignedBuildings,
            loginUrl: $loginUrl,
            isReset: $isReset
        );
    }
}
