<?php

namespace App\Http\Requests\V1\Ticket;

class UpdateTicketRequest extends StoreTenantTicketRequest
{
    /**
     * Quy tắc cập nhật thông tin phiếu yêu cầu kỹ thuật.
     * Kế thừa từ StoreTenantTicketRequest và chỉ cho phép cập nhật các trường được phép sửa.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedFields = ['title', 'description', 'category', 'priority', 'unit_id'];
        $parentRules = parent::rules();
        $rules = [];

        foreach ($allowedFields as $field) {
            if (isset($parentRules[$field])) {
                $fieldRules = $parentRules[$field];
                $rules[$field] = is_array($fieldRules)
                    ? array_map(fn ($r) => $r === 'required' ? 'sometimes' : $r, $fieldRules)
                    : $fieldRules;
                if (!in_array('sometimes', $rules[$field], true)) {
                    array_unshift($rules[$field], 'sometimes');
                }
            }
        }

        return $rules;
    }
}
