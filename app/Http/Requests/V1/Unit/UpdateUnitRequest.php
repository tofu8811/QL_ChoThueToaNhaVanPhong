<?php

namespace App\Http\Requests\V1\Unit;

class UpdateUnitRequest extends StoreUnitRequest
{
    /**
     * Quy tắc xác thực cập nhật thông số Mặt bằng văn phòng.
     * Kế thừa toàn bộ từ StoreUnitRequest.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['building_id']);

        foreach ($rules as $field => $fieldRules) {
            if (is_array($fieldRules)) {
                $rules[$field] = array_map(function ($rule) {
                    return $rule === 'required' ? 'sometimes' : $rule;
                }, $fieldRules);
            }
        }

        return $rules;
    }
}
