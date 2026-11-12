<?php

namespace App\Http\Requests\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền cập nhật Lead hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực cập nhật thông tin nhu cầu của Lead.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_full_name' => ['sometimes', 'required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['sometimes', 'required', 'email', 'max:150'],
            'lead_source' => ['nullable', 'string', 'in:WEBSITE,HOTLINE,DIRECT_WALK_IN,BROKER,FACEBOOK_ADS,GOOGLE_ADS,REFERRAL'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'unit_id' => ['nullable', 'uuid', 'exists:units,id'],
            'desired_area_sqm' => ['nullable', 'numeric', 'min:1'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'preferred_site_tour_at' => ['nullable', 'date'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
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
            'budget_max.gte' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.',
        ];
    }
}
