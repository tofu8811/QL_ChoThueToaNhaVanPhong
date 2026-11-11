<?php

namespace App\Http\Requests\V1\Unit;

use App\Enums\UnitStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực cập nhật thông số Mặt bằng văn phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'unit_code' => ['sometimes', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'floor_number' => ['sometimes', 'integer'],
            'area_sqm' => ['sometimes', 'numeric', 'min:1'],
            'base_rental_price' => ['sometimes', 'numeric', 'min:0'],
            'handover_condition' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', 'string', 'max:50'],
            'floor_plan_image_url' => ['nullable', 'string', 'max:500'],
            'available_from_date' => ['nullable', 'date'],
            'status' => ['sometimes', new Enum(UnitStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'area_sqm.min' => 'Diện tích cho thuê phải lớn hơn 0.',
            'base_rental_price.min' => 'Đơn giá thuê cơ sở phải lớn hơn hoặc bằng 0.',
        ];
    }
}
