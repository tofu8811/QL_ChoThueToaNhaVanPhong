<?php

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\BaseApiRequest;
use Illuminate\Validation\Rules\Password;

/**
 * FormRequest kiểm thực dữ liệu đổi mật khẩu cá nhân (/api/v1/auth/password).
 */
class ChangePasswordRequest extends BaseApiRequest
{
    /**
     * Quy tắc kiểm thực dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'password_confirmation' => ['required', 'string'],
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
            'current_password.required'      => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required'              => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed'             => 'Mật khẩu xác nhận không khớp với mật khẩu mới.',
            'password.min'                   => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'password_confirmation.required' => 'Vui lòng xác nhận lại mật khẩu mới.',
        ];
    }
}
