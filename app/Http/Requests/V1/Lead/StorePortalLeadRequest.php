<?php

namespace App\Http\Requests\V1\Lead;

class StorePortalLeadRequest extends StoreCrmLeadRequest
{
    /**
     * Quy tắc xác thực dữ liệu gửi form từ Public Portal.
     * Kế thừa từ StoreCrmLeadRequest nhưng giới hạn các trường nội bộ CRM
     * và yêu cầu thời gian khảo sát thực tế phải cách ít nhất 3 ngày.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['lead_source'], $rules['assigned_sales_id']);

        $rules['preferred_site_tour_at'] = [
            'nullable',
            'date',
            'after_or_equal:' . now()->addDays(3)->format('Y-m-d H:i:s'),
        ];

        return $rules;
    }

    /**
     * Thông báo lỗi tiếng Việt tùy chỉnh.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'preferred_site_tour_at.after_or_equal' => 'Thời gian muốn khảo sát thực tế phải cách thời điểm gửi yêu cầu ít nhất 3 ngày (từ ngày ' . now()->addDays(3)->format('d/m/Y H:i') . ' trở đi).',
        ]);
    }
}
