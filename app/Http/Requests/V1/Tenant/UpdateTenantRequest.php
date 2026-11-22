<?php

namespace App\Http\Requests\V1\Tenant;

use Illuminate\Validation\Rule;

class UpdateTenantRequest extends StoreTenantRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->getResourceId('tenant') ?? $this->getResourceId();
        $rules = parent::rules();
        unset($rules['password'], $rules['business_license_private_path'], $rules['converted_from_lead_id']);

        foreach ($rules as $field => $fieldRules) {
            if (is_array($fieldRules)) {
                $rules[$field] = array_map(fn ($r) => $r === 'required' ? 'sometimes' : $r, $fieldRules);
                if (!in_array('sometimes', $rules[$field], true)) {
                    array_unshift($rules[$field], 'sometimes');
                }
            }
        }

        $rules['tax_code'] = ['sometimes', 'required', 'string', 'max:30', Rule::unique('tenants', 'tax_code')->ignore($tenantId)];
        $rules['login_email'] = ['sometimes', 'required', 'email', 'max:150', Rule::unique('tenants', 'login_email')->ignore($tenantId)];
        $rules['status'] = ['nullable', 'string', Rule::in(['ACTIVE', 'LOCKED', 'PENDING'])];

        return $rules;
    }
}
