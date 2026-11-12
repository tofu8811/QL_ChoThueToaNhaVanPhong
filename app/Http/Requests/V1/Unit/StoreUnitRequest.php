<?php

namespace App\Http\Requests\V1\Unit;

use App\Enums\UnitStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực thêm mới Mặt bằng văn phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_id' => ['required', 'string', 'exists:buildings,id'],
            'unit_code' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'floor_number' => ['required', 'integer'],
            'area_sqm' => ['required', 'numeric', 'min:1'],
            'base_rental_price' => ['required', 'numeric', 'min:0'],
            'handover_condition' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', 'string', 'max:50'],
            'floor_plan_image_url' => ['nullable', 'string', 'max:500'],
            'available_from_date' => ['nullable', 'date'],
            'status' => ['nullable', new Enum(UnitStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'building_id.required' => 'Mã tòa nhà là bắt buộc.',
            'building_id.exists' => 'Tòa nhà đã chọn không tồn tại trên hệ thống.',
            'unit_code.required' => 'Mã căn/phòng là bắt buộc.',
            'floor_number.required' => 'Số tầng là bắt buộc.',
            'area_sqm.required' => 'Diện tích cho thuê (m2) là bắt buộc và phải lớn hơn 0.',
            'base_rental_price.required' => 'Đơn giá thuê cơ sở là bắt buộc.',
        ];
    }
}
