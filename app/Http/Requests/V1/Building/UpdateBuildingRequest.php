<?php

namespace App\Http\Requests\V1\Building;

use Illuminate\Validation\Rule;

class UpdateBuildingRequest extends StoreBuildingRequest
{
    /**
     * Quy tắc cập nhật Tòa nhà văn phòng.
     * Kế thừa toàn bộ quy tắc từ StoreBuildingRequest và linh hoạt chuyển đổi sang 'sometimes'.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $buildingId = $this->getResourceId('building') ?? $this->getResourceId();
        $rules = parent::rules();

        foreach ($rules as $field => $fieldRules) {
            if (is_array($fieldRules)) {
                $rules[$field] = array_map(function ($rule) {
                    return $rule === 'required' ? 'sometimes' : $rule;
                }, $fieldRules);
            }
        }

        // Ghi đè riêng unique check cho building_code khi cập nhật
        $rules['building_code'] = ['sometimes', 'string', 'max:50', Rule::unique('buildings', 'building_code')->ignore($buildingId)];

        return $rules;
    }
}
