<?php

namespace App\Http\Requests\V1\Auth;

use App\Enums\UserRole;
use App\Http\Requests\V1\BaseApiRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest kiểm thực dữ liệu đầu vào cho API Đăng nhập Đa tác nhân (/api/v1/auth/login).
 */
class LoginRequest extends BaseApiRequest
{
    /**
     * Quy tắc kiểm thực dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role'     => ['nullable', 'string', Rule::in(UserRole::values())],
        ];
    }

    /**
     * Thông báo lỗi kiểm thực tùy chỉnh bằng tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'Vui lòng nhập địa chỉ email.',
            'email.email'       => 'Địa chỉ email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'role.in'           => 'Vai trò đăng nhập không hợp lệ.',
        ];
    }
}
