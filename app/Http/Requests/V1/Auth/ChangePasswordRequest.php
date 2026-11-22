<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Class ChangePasswordRequest
 * 
 * FormRequest kiểm thực dữ liệu đổi mật khẩu cá nhân (/api/v1/auth/password).
 * Yêu cầu: tối thiểu 8 ký tự, có chữ hoa, thường, số và ký tự đặc biệt.
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này hay không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

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
            'password.mixed'                 => 'Mật khẩu mới phải bao gồm cả chữ hoa và chữ thường.',
            'password.letters'               => 'Mật khẩu mới phải chứa ít nhất một chữ cái.',
            'password.numbers'               => 'Mật khẩu mới phải chứa ít nhất một chữ số.',
            'password.symbols'               => 'Mật khẩu mới phải chứa ít nhất một ký tự đặc biệt.',
            'password_confirmation.required' => 'Vui lòng xác nhận lại mật khẩu mới.',
        ];
    }
}
