<?php

namespace App\Http\Requests\V1\Lead;

use App\Http\Requests\V1\BaseApiRequest;
use App\Rules\VietnamesePhoneNumber;
use Illuminate\Validation\Rule;

class ConvertLeadToTenantRequest extends BaseApiRequest
{
    /**
     * Quy tắc xác thực chuyển đổi Lead sang Khách thuê chính thức (Tenant).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tax_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tenants', 'tax_code')->whereNull('deleted_at'),
            ],
            'company_name' => ['nullable', 'string', 'max:200'],
            'representative_name' => ['nullable', 'string', 'max:120'],
            'representative_position' => ['nullable', 'string', 'max:100'],
            'headquarter_address' => ['nullable', 'string', 'max:255'],
            'business_license_private_path' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', new VietnamesePhoneNumber],
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
            'tax_code.unique' => 'Mã số thuế này đã tồn tại trên hồ sơ khách thuê khác trong hệ thống.',
        ];
    }
}
