<?php

namespace App\Http\Requests\V1\Accountant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAccountantRequest extends FormRequest
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
            'employee_code'          => ['nullable', 'string', 'max:30', Rule::unique('accountants', 'employee_code')->whereNull('deleted_at')],
            'full_name'              => ['required', 'string', 'max:120'],
            'email'                  => ['required', 'string', 'email', 'max:150', Rule::unique('accountants', 'email')->whereNull('deleted_at')],
            'phone'                  => ['nullable', 'string', 'max:20', Rule::unique('accountants', 'phone')->whereNull('deleted_at')],
            'password'               => ['required', 'string', Password::min(8)],
            'can_approve_adjustment' => ['nullable', 'boolean'],
            'status'                 => ['nullable', 'string', 'in:ACTIVE,LOCKED,PENDING'],
            'building_ids'           => ['nullable', 'array'],
            'building_ids.*'         => ['string', 'uuid', 'exists:buildings,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required'    => 'Họ và tên nhân viên Kế toán là bắt buộc.',
            'full_name.max'         => 'Họ và tên không được vượt quá 120 ký tự.',
            'email.required'        => 'Email đăng nhập là bắt buộc.',
            'email.email'           => 'Email không đúng định dạng.',
            'email.unique'          => 'Email này đã được sử dụng bởi một tài khoản kế toán khác.',
            'phone.unique'          => 'Số điện thoại này đã được sử dụng bởi một tài khoản kế toán khác.',
            'password.required'     => 'Mật khẩu là bắt buộc.',
            'password.min'          => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'building_ids.*.exists' => 'Mã tòa nhà được phân công không tồn tại trong hệ thống.',
        ];
    }
}
