<?php

namespace App\Http\Requests\V1\Lead;

class UpdateLeadRequest extends StoreCrmLeadRequest
{
    /**
     * Quy tắc xác thực cập nhật thông tin nhu cầu của Lead.
     * Kế thừa từ StoreCrmLeadRequest.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['assigned_sales_id']);

        foreach ($rules as $field => $fieldRules) {
            if (is_array($fieldRules)) {
                $rules[$field] = array_map(fn ($r) => $r === 'required' ? 'sometimes' : $r, $fieldRules);
                if (!in_array('sometimes', $rules[$field], true)) {
                    array_unshift($rules[$field], 'sometimes');
                }
            }
        }

        return $rules;
    }
}
