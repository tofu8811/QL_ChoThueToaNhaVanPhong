<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
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
        $adminId = $this->route('id');

        return [
            'full_name'      => ['sometimes', 'required', 'string', 'max:120'],
            'email'          => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('admins', 'email')->ignore($adminId)->whereNull('deleted_at'),
            ],
            'phone'          => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('admins', 'phone')->ignore($adminId)->whereNull('deleted_at'),
            ],
            'password'       => [
                'sometimes',
                'nullable',
                'string',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'is_super_admin' => ['sometimes', 'boolean'],
            'status'         => ['sometimes', 'string', 'in:ACTIVE,LOCKED,PENDING'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên quản trị viên không được để trống.',
            'full_name.max'      => 'Họ và tên không được vượt quá 120 ký tự.',
            'email.required'     => 'Email không được để trống.',
            'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã tồn tại trong hệ thống.',
            'phone.unique'       => 'Số điện thoại này đã tồn tại trong hệ thống.',
            'password.min'       => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'status.in'          => 'Trạng thái tài khoản không hợp lệ (ACTIVE, LOCKED, PENDING).',
        ];
    }
}
