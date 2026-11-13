<?php

namespace App\Http\Requests\V1\PropertyManager;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdatePropertyManagerRequest extends FormRequest
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
        $pmId = $this->route('id');

        return [
            'full_name'              => ['sometimes', 'required', 'string', 'max:120'],
            'email'                  => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('property_managers', 'email')->ignore($pmId)->whereNull('deleted_at'),
            ],
            'phone'                  => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('property_managers', 'phone')->ignore($pmId)->whereNull('deleted_at'),
            ],
            'emergency_hotline'      => ['sometimes', 'nullable', 'string', 'max:20'],
            'password'               => ['sometimes', 'nullable', 'string', Password::min(8)],
            'department'             => ['sometimes', 'string', 'in:TECHNICAL,SECURITY,CLEANING,CUSTOMER_CARE'],
            'status'                 => ['sometimes', 'string', 'in:ACTIVE,LOCKED,PENDING'],
            'building_ids'           => ['sometimes', 'nullable', 'array'],
            'building_ids.*'         => ['string', 'uuid', 'exists:buildings,id'],
            'is_primary_building_id' => ['nullable', 'string', 'uuid', 'exists:buildings,id'],
        ];
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
        return [
            'full_name.required'    => 'Họ và tên nhân sự Ban Quản lý không được để trống.',
            'email.required'        => 'Email không được để trống.',
            'email.unique'          => 'Email này đã tồn tại trong hệ thống.',
            'phone.unique'          => 'Số điện thoại này đã tồn tại trong hệ thống.',
            'password.min'          => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'department.in'         => 'Bộ phận chuyên trách không hợp lệ (TECHNICAL, SECURITY, CLEANING, CUSTOMER_CARE).',
            'building_ids.*.exists' => 'Mã tòa nhà được phân công không tồn tại trong hệ thống.',
        ];
    }
}
