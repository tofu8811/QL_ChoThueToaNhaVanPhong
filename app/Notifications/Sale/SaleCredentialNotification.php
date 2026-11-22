<?php

namespace App\Notifications\Sale;

use App\Models\Sale;
use App\Notifications\Concerns\BaseStaffCredentialNotification;

/**
 * Notification gửi thông tin tài khoản hoặc cấp lại mật khẩu cho Chuyên viên Kinh doanh (Sales).
 */
class SaleCredentialNotification extends BaseStaffCredentialNotification
{
    /**
     * Khởi tạo thông báo cho Sales.
     *
     * @param string $plainPassword
     * @param Sale $sale
     * @param array<int, string> $assignedBuildings
     * @param bool $isReset
     */
    public function __construct(
        string $plainPassword,
        Sale $sale,
        array $assignedBuildings = [],
        bool $isReset = false
    ) {
        $loginUrl = route('sales.dashboard');

        parent::__construct(
            plainPassword: $plainPassword,
            roleName: 'Chuyên viên Kinh doanh & Cho thuê (Sales)',
            employeeCode: $sale->employee_code,
            assignedBuildings: $assignedBuildings,
            loginUrl: $loginUrl,
            isReset: $isReset
        );
    }
}
