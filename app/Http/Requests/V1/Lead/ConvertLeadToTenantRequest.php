<?php

namespace App\Http\Requests\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class ConvertLeadToTenantRequest extends FormRequest
{
    /**
     * Xác định quyền chuyển đổi Lead thành khách thuê chính thức.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực chuyển đổi Lead sang Khách thuê chính thức (Tenant).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tax_code' => ['required', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'representative_name' => ['nullable', 'string', 'max:120'],
            'representative_position' => ['nullable', 'string', 'max:100'],
            'headquarter_address' => ['nullable', 'string', 'max:255'],
            'business_license_private_path' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tùy chỉnh.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tax_code.required' => 'Mã số thuế doanh nghiệp là bắt buộc khi chuyển đổi thành khách thuê chính thức.',
        ];
    }
}
