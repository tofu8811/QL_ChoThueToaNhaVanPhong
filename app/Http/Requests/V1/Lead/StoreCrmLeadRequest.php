<?php

namespace App\Http\Requests\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrmLeadRequest extends FormRequest
{
    /**
     * Xác định xem nhân viên có quyền tạo Lead CRM thủ công hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực khi tạo Lead thủ công từ CRM.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_full_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150'],
            'lead_source' => ['nullable', 'string', 'in:WEBSITE,HOTLINE,DIRECT_WALK_IN,BROKER,FACEBOOK_ADS,GOOGLE_ADS,REFERRAL'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'unit_id' => ['nullable', 'uuid', 'exists:units,id'],
            'desired_area_sqm' => ['nullable', 'numeric', 'min:1'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'preferred_site_tour_at' => ['nullable', 'date'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'assigned_sales_id' => ['nullable', 'uuid', 'exists:sales,id'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tùy chỉnh.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_full_name.required' => 'Họ và tên người liên hệ là bắt buộc.',
            'phone.required' => 'Số điện thoại liên hệ là bắt buộc.',
            'email.required' => 'Địa chỉ email là bắt buộc.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'budget_max.gte' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.',
        ];
    }
}
