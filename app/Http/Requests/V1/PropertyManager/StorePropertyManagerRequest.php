<?php

namespace App\Http\Requests\V1\PropertyManager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StorePropertyManagerRequest extends FormRequest
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
            'employee_code'          => ['nullable', 'string', 'max:30', Rule::unique('property_managers', 'employee_code')->whereNull('deleted_at')],
            'full_name'              => ['required', 'string', 'max:120'],
            'email'                  => ['required', 'string', 'email', 'max:150', Rule::unique('property_managers', 'email')->whereNull('deleted_at')],
            'phone'                  => ['nullable', 'string', 'max:20', Rule::unique('property_managers', 'phone')->whereNull('deleted_at')],
            'emergency_hotline'      => ['nullable', 'string', 'max:20'],
            'password'               => ['required', 'string', Password::min(8)],
            'department'             => ['nullable', 'string', 'in:TECHNICAL,SECURITY,CLEANING,CUSTOMER_CARE'],
            'status'                 => ['nullable', 'string', 'in:ACTIVE,LOCKED,PENDING'],
            'building_ids'           => ['nullable', 'array'],
            'building_ids.*'         => ['string', 'uuid', 'exists:buildings,id'],
            'is_primary_building_id' => ['nullable', 'string', 'uuid', 'exists:buildings,id'],
        ];
    }

    /**
     * Chuẩn bị dữ liệu trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('phone') && $this->has('emergency_hotline')) {
            $this->merge(['phone' => $this->emergency_hotline]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required'    => 'Họ và tên nhân sự Ban Quản lý là bắt buộc.',
            'full_name.max'         => 'Họ và tên không được vượt quá 120 ký tự.',
            'email.required'        => 'Email đăng nhập là bắt buộc.',
            'email.email'           => 'Email không đúng định dạng.',
            'email.unique'          => 'Email này đã được sử dụng bởi một tài khoản BQL khác.',
            'phone.unique'          => 'Số điện thoại này đã được sử dụng bởi một tài khoản BQL khác.',
            'password.required'     => 'Mật khẩu là bắt buộc.',
            'password.min'          => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'department.in'         => 'Bộ phận chuyên trách không hợp lệ (TECHNICAL, SECURITY, CLEANING, CUSTOMER_CARE).',
            'building_ids.*.exists' => 'Mã tòa nhà được phân công không tồn tại trong hệ thống.',
        ];
    }
}
