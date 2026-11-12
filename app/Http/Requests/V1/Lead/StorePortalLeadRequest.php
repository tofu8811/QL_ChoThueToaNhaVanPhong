<?php

namespace App\Http\Requests\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class StorePortalLeadRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền gửi yêu cầu tư vấn công khai hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực dữ liệu gửi form từ Public Portal.
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
            'building_id' => ['required', 'uuid', 'exists:buildings,id'],
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
            'contact_full_name.required' => 'Họ và tên người liên hệ là bắt buộc.',
            'phone.required' => 'Số điện thoại liên hệ là bắt buộc.',
            'email.required' => 'Địa chỉ email là bắt buộc.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'building_id.required' => 'Tòa nhà văn phòng quan tâm là bắt buộc.',
            'building_id.exists' => 'Tòa nhà văn phòng không tồn tại trong hệ thống.',
            'unit_id.exists' => 'Mặt bằng văn phòng đã chọn không tồn tại.',
            'budget_max.gte' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.',
        ];
    }
}
