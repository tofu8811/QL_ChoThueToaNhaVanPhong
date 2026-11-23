<?php

namespace App\Http\Requests\V1\Unit;

use App\Enums\UnitStatus;
use App\Http\Requests\V1\BaseApiRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUnitRequest extends BaseApiRequest
{
    /**
     * Chuẩn hóa dữ liệu đầu vào:
     * Hỗ trợ alias từ các Form UI (area -> area_sqm, rent_price_per_sqm -> base_rental_price)
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        if (!$this->has('area_sqm') && $this->has('area')) {
            $merge['area_sqm'] = $this->input('area');
        }
        if (!$this->has('base_rental_price') && $this->has('rent_price_per_sqm')) {
            $merge['base_rental_price'] = $this->input('rent_price_per_sqm');
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Quy tắc xác thực thêm mới Mặt bằng văn phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_id' => [
                'required',
                'string',
                Rule::exists('buildings', 'id')->whereNull('deleted_at'),
            ],
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
            'building_id.exists' => 'Tòa nhà đã chọn không tồn tại trên hệ thống.',
            'area_sqm.min' => 'Diện tích cho thuê (m2) phải lớn hơn 0.',
            'base_rental_price.min' => 'Đơn giá thuê cơ sở phải lớn hơn hoặc bằng 0.',
        ];
    }
}
