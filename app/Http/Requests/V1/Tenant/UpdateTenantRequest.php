<?php

namespace App\Http\Requests\V1\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
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
        $tenantId = $this->route('id') ?? $this->route('tenant');

        return [
            'company_name' => ['sometimes', 'required', 'string', 'max:200'],
            'tax_code' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('tenants', 'tax_code')->ignore($tenantId)],
            'representative_name' => ['sometimes', 'required', 'string', 'max:120'],
            'representative_position' => ['nullable', 'string', 'max:100'],
            'headquarter_address' => ['sometimes', 'required', 'string', 'max:255'],
            'login_email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('tenants', 'login_email')->ignore($tenantId)],
            'billing_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['sometimes', 'required', 'string', 'max:20'],
            'status' => ['nullable', 'string', Rule::in(['ACTIVE', 'LOCKED', 'PENDING'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tax_code.unique' => 'Mã số thuế này đã tồn tại trong hệ thống.',
            'login_email.unique' => 'Email đăng nhập này đã được sử dụng.',
        ];
    }
}
