<?php

namespace App\Http\Requests\V1\Contract;

use Illuminate\Foundation\Http\FormRequest;

class AttachContractUnitRequest extends FormRequest
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
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'leased_area_sqm' => ['required', 'numeric', 'gt:0'],
            'agreed_rental_price_per_sqm' => ['required', 'numeric', 'gt:0'],
            'agreed_service_fee_per_sqm' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_id.required' => 'Mã mặt bằng văn phòng là bắt buộc.',
            'unit_id.exists' => 'Mặt bằng không tồn tại trong hệ thống.',
            'leased_area_sqm.required' => 'Diện tích thuê chốt là bắt buộc.',
            'leased_area_sqm.gt' => 'Diện tích thuê phải lớn hơn 0.',
            'agreed_rental_price_per_sqm.required' => 'Đơn giá thuê chốt là bắt buộc.',
            'agreed_rental_price_per_sqm.gt' => 'Đơn giá thuê phải lớn hơn 0.',
        ];
    }
}
