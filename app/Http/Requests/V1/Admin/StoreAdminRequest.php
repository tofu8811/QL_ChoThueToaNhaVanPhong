<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name'      => ['required', 'string', 'max:120'],
            'email'          => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('admins', 'email')->whereNull('deleted_at'),
            ],
            'phone'          => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('admins', 'phone')->whereNull('deleted_at'),
            ],
            'password'       => [
                'required',
                'string',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'is_super_admin' => ['nullable', 'boolean'],
            'status'         => ['nullable', 'string', 'in:ACTIVE,LOCKED,PENDING'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên quản trị viên là bắt buộc.',
            'full_name.max'      => 'Họ và tên không được vượt quá 120 ký tự.',
            'email.required'     => 'Email đăng nhập là bắt buộc.',
            'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã được sử dụng bởi một tài khoản quản trị khác.',
            'phone.unique'       => 'Số điện thoại này đã được sử dụng bởi một tài khoản quản trị khác.',
            'password.required'  => 'Mật khẩu là bắt buộc.',
            'password.min'       => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'status.in'          => 'Trạng thái tài khoản không hợp lệ (ACTIVE, LOCKED, PENDING).',
        ];
    }
}
