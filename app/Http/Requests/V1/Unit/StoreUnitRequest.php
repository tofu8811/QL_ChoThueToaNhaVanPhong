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
     * Tự động sinh mã sàn (unit_code) nếu để trống khi đã có building_id và floor_number.
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
        if (!$this->filled('handover_condition')) {
            $merge['handover_condition'] = 'BASIC';
        }

        // Tự động sinh mã sàn nếu để trống hoặc null khi đã có tòa nhà và tầng
        if ($this->filled('building_id') && $this->filled('floor_number') && !$this->filled('unit_code')) {
            try {
                $code = app(\App\Services\UnitService::class)->generateUnitCode(
                    $this->input('building_id'),
                    (int) $this->input('floor_number')
                );
                $merge['unit_code'] = $code;
            } catch (\Throwable $e) {
                // Nếu floor hoặc building chưa hợp lệ, để validator phía sau xử lý
            }
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

    /**
     * Bổ sung kiểm tra vị trí tầng (floor_number) phù hợp với quy mô tòa nhà.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('floor_number')) {
                $buildingId = $this->input('building_id');
                if (!$buildingId) {
                    $unitId = $this->route('unit') ?? $this->route('id');
                    if ($unitId) {
                        $unit = \App\Models\Unit::find($unitId);
                        $buildingId = $unit?->building_id;
                    }
                }

                if ($buildingId) {
                    $building = \App\Models\Building::find($buildingId);
                    if ($building) {
                        $floor = (int) $this->input('floor_number');
                        if ($floor === 0) {
                            $rangeText = "từ tầng 1 đến tầng {$building->total_floors}";
                            if ($building->total_basements > 0) {
                                $rangeText .= " hoặc tầng hầm từ B1 đến B{$building->total_basements} (nhập -1 đến -{$building->total_basements})";
                            }
                            $validator->errors()->add('floor_number', "Vị trí tầng không hợp lệ (tòa nhà không có tầng 0). Vui lòng chọn {$rangeText}.");
                        } elseif ($floor > 0 && $floor > (int) $building->total_floors) {
                            $validator->errors()->add('floor_number', "Vị trí tầng ({$floor}) vượt quá số tầng của tòa nhà (tối đa {$building->total_floors} tầng nổi).");
                        } elseif ($floor < 0) {
                            $basements = (int) ($building->total_basements ?? 0);
                            if ($basements <= 0) {
                                $validator->errors()->add('floor_number', "Tòa nhà không có tầng hầm. Vị trí tầng phải từ 1 đến {$building->total_floors}.");
                            } elseif (abs($floor) > $basements) {
                                $validator->errors()->add('floor_number', "Vị trí tầng hầm ({$floor}) vượt quá số tầng hầm của tòa nhà (tối đa {$basements} tầng hầm: từ B1 đến B{$basements}).");
                            }
                        }
                    }
                }
            }
        });
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
