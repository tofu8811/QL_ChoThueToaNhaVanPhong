<?php

namespace App\Http\Requests\V1\PropertyManager;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\HasStaffRules;
use App\Rules\VietnamesePhoneNumber;

class UpdatePropertyManagerRequest extends BaseApiRequest
{
    use HasStaffRules;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pmId = $this->getResourceId();

        return array_merge(
            $this->staffAccountRules('property_managers', $pmId, true, true, false),
            [
                'emergency_hotline'      => ['sometimes', 'nullable', 'string', new VietnamesePhoneNumber],
                'department'             => ['sometimes', 'string', 'in:TECHNICAL,SECURITY,CLEANING,CUSTOMER_CARE'],
                'is_primary_building_id' => ['nullable', 'string', 'uuid', 'exists:buildings,id'],
            ]
        );
    }

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
        return array_merge($this->staffAccountMessages('Ban Quản lý'), [
            'department.in' => 'Bộ phận chuyên trách không hợp lệ (TECHNICAL, SECURITY, CLEANING, CUSTOMER_CARE).',
        ]);
    }
}
