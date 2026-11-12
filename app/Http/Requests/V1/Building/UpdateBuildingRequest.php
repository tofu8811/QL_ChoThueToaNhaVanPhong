<?php

namespace App\Http\Requests\V1\Building;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc cập nhật Tòa nhà văn phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $buildingId = $this->route('building') ? ($this->route('building')->id ?? $this->route('building')) : null;

        return [
            'building_code' => ['sometimes', 'string', 'max:50', "unique:buildings,building_code,{$buildingId},id"],
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:100'],
            'district' => ['sometimes', 'string', 'max:100'],
            'province' => ['sometimes', 'string', 'max:100'],
            'province_code' => ['sometimes', 'integer', 'exists:provinces,code'],
            'district_code' => ['nullable', 'integer'],
            'ward_code' => ['nullable', 'integer'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'total_floors' => ['sometimes', 'integer', 'min:1'],
            'total_basements' => ['nullable', 'integer', 'min:0'],
            'total_leasable_area_sqm' => ['sometimes', 'numeric', 'min:1'],
            'ceiling_height_meters' => ['nullable', 'numeric', 'min:1'],
            'elevators_count' => ['nullable', 'integer', 'min:0'],
            'air_conditioner_spec' => ['nullable', 'string', 'max:255'],
            'backup_generator_spec' => ['nullable', 'string', 'max:255'],
            'working_hours_desc' => ['nullable', 'string', 'max:255'],
            'overtime_fee_per_hour' => ['nullable', 'numeric', 'min:0'],
            'motorbike_parking_fee' => ['nullable', 'numeric', 'min:0'],
            'car_parking_fee' => ['nullable', 'numeric', 'min:0'],
            'electricity_unit_price' => ['sometimes', 'numeric', 'min:0'],
            'water_unit_price' => ['sometimes', 'numeric', 'min:0'],
            'default_service_fee_per_sqm' => ['sometimes', 'numeric', 'min:0'],
            'min_rent_price_per_sqm' => ['sometimes', 'numeric', 'min:0'],
            'max_rent_price_per_sqm' => ['sometimes', 'numeric'],
            'deposit_term_months' => ['nullable', 'integer', 'min:1'],
            'payment_term_months' => ['nullable', 'integer', 'min:1'],
            'grade' => ['sometimes', 'string', 'in:A,B,C,a,b,c'],
            'direction' => ['nullable', 'string', 'max:50'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'building_code.unique' => 'Mã định danh tòa nhà đã tồn tại trên hệ thống.',
            'province_code.exists' => 'Mã tỉnh/thành phố không tồn tại trong danh mục.',
            'grade.in' => 'Phân hạng văn phòng chỉ chấp nhận hạng A, B hoặc C.',
        ];
    }
}
