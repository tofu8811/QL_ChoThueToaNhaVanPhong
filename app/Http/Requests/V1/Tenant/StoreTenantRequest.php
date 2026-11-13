<?php

namespace App\Http\Requests\V1\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:200'],
            'tax_code' => ['required', 'string', 'max:30', 'unique:tenants,tax_code'],
            'representative_name' => ['required', 'string', 'max:120'],
            'representative_position' => ['nullable', 'string', 'max:100'],
            'headquarter_address' => ['required', 'string', 'max:255'],
            'login_email' => ['required', 'email', 'max:150', 'unique:tenants,login_email'],
            'billing_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'business_license_private_path' => ['nullable', 'string', 'max:255'],
            'converted_from_lead_id' => ['nullable', 'uuid', 'exists:leads,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Tên doanh nghiệp là bắt buộc.',
            'tax_code.required' => 'Mã số thuế doanh nghiệp là bắt buộc.',
            'tax_code.unique' => 'Mã số thuế này đã tồn tại trong hệ thống.',
            'representative_name.required' => 'Tên người đại diện pháp luật là bắt buộc.',
            'headquarter_address.required' => 'Địa chỉ trụ sở chính theo ĐKKD là bắt buộc.',
            'login_email.required' => 'Email đăng nhập Portal là bắt buộc.',
            'login_email.unique' => 'Email đăng nhập này đã được sử dụng.',
            'contact_phone.required' => 'Số điện thoại liên hệ là bắt buộc.',
        ];
    }
}
