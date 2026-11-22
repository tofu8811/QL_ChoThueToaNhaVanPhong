<?php

namespace App\Http\Requests\V1\Traits;

use App\Rules\VietnamesePhoneNumber;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Trait cung cấp các quy tắc xác thực chung cho các tài khoản nhân sự nội bộ
 * (Admin, Kế toán / Accountant, Chuyên viên Sales, Ban Quản lý / Property Manager).
 */
trait HasStaffRules
{
    /**
     * Tạo bộ quy tắc xác thực chuẩn cho tài khoản nhân sự.
     *
     * @param string $table Tên bảng CSDL (admins, accountants, sales, property_managers)
     * @param string|null $ignoreId ID bỏ qua khi cập nhật (null khi tạo mới)
     * @param bool $isUpdate Cờ cập nhật hay tạo mới
     * @param bool $includeBuildingIds Có kiểm tra phân công danh sách tòa nhà hay không
     * @param bool $strongPassword Có yêu cầu mật khẩu độ phức tạp cao (cho Admin) hay không
     * @return array<string, mixed>
     */
    protected function staffAccountRules(
        string $table,
        ?string $ignoreId = null,
        bool $isUpdate = false,
        bool $includeBuildingIds = true,
        bool $strongPassword = false
    ): array {
        $prefix = $isUpdate ? 'sometimes' : 'required';

        $passwordRule = $strongPassword
            ? Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            : Password::min(8);

        $rules = [
            'employee_code' => ['nullable', 'string', 'max:30', Rule::unique($table, 'employee_code')->ignore($ignoreId)->whereNull('deleted_at')],
            'full_name'     => [$prefix, 'string', 'max:120'],
            'email'         => [
                $prefix,
                'string',
                'email',
                'max:150',
                Rule::unique($table, 'email')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'phone'         => [
                'sometimes',
                'nullable',
                'string',
                new VietnamesePhoneNumber,
                Rule::unique($table, 'phone')->ignore($ignoreId)->whereNull('deleted_at'),
            ],
            'password'      => ['sometimes', 'nullable', 'string', $passwordRule],
            'status'        => [$isUpdate ? 'sometimes' : 'nullable', 'string', 'in:ACTIVE,LOCKED,PENDING'],
        ];

        if ($includeBuildingIds) {
            $rules['building_ids']   = ['sometimes', 'nullable', 'array'];
            $rules['building_ids.*'] = ['string', 'uuid', 'exists:buildings,id'];
        }

        return $rules;
    }

    /**
     * Bộ thông báo lỗi tiếng Việt thân thiện dùng chung cho nhân sự.
     */
    protected function staffAccountMessages(string $roleTitle = 'nhân sự'): array
    {
        return [
            'full_name.required'    => "Họ và tên {$roleTitle} là bắt buộc.",
            'full_name.max'         => 'Họ và tên không được vượt quá 120 ký tự.',
            'email.required'        => 'Email đăng nhập là bắt buộc.',
            'email.email'           => 'Email không đúng định dạng.',
            'email.unique'          => "Email này đã được sử dụng bởi một tài khoản khác.",
            'phone.unique'          => "Số điện thoại này đã được sử dụng bởi một tài khoản khác.",
            'password.required'     => 'Mật khẩu là bắt buộc.',
            'password.min'          => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'building_ids.*.exists' => 'Mã tòa nhà được phân công không tồn tại trong hệ thống.',
        ];
    }
}
