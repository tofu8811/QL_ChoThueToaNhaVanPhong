<?php

namespace App\Http\Requests\V1\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreSaleRequest extends FormRequest
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
            'employee_code'        => ['nullable', 'string', 'max:30', Rule::unique('sales', 'employee_code')->whereNull('deleted_at')],
            'full_name'            => ['required', 'string', 'max:120'],
            'email'                => ['required', 'string', 'email', 'max:150', Rule::unique('sales', 'email')->whereNull('deleted_at')],
            'phone'                => ['nullable', 'string', 'max:20', Rule::unique('sales', 'phone')->whereNull('deleted_at')],
            'password'             => ['required', 'string', Password::min(8)],
            'commission_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_concurrent_leads' => ['nullable', 'integer', 'min:1'],
            'status'               => ['nullable', 'string', 'in:ACTIVE,LOCKED,PENDING'],
            'building_ids'         => ['nullable', 'array'],
            'building_ids.*'       => ['string', 'uuid', 'exists:buildings,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên nhân viên Sales là bắt buộc.',
            'full_name.max'      => 'Họ và tên không được vượt quá 120 ký tự.',
            'email.required'     => 'Email đăng nhập là bắt buộc.',
            'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã được sử dụng bởi một nhân viên khác.',
            'phone.unique'       => 'Số điện thoại này đã được sử dụng bởi một nhân viên khác.',
            'password.required'  => 'Mật khẩu là bắt buộc.',
            'password.min'       => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'building_ids.*.exists' => 'Mã tòa nhà được phân công không tồn tại trong hệ thống.',
        ];
    }
}
