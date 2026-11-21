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
     * Chuẩn hóa dữ liệu đầu vào trước khi xác thực.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9]/', '', (string) $this->phone),
            ]);
        }
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
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9,10}$/'],
            'email' => ['required', 'string', 'email:rfc,filter', 'max:150'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'unit_id' => ['nullable', 'uuid', 'exists:units,id'],
            'desired_area_sqm' => ['nullable', 'numeric', 'min:1'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'preferred_site_tour_at' => [
                'nullable',
                'date',
                'after_or_equal:' . now()->addDays(3)->format('Y-m-d H:i:s'),
            ],
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
            'phone.required' => 'Vui lòng cung cấp số điện thoại hợp lệ.',
            'phone.regex' => 'Vui lòng cung cấp số điện thoại hợp lệ.',
            'email.required' => 'Vui lòng cung cấp địa chỉ email hợp lệ.',
            'email.email' => 'Vui lòng cung cấp địa chỉ email hợp lệ.',
            'building_id.exists' => 'Tòa nhà văn phòng không tồn tại trong hệ thống.',
            'unit_id.exists' => 'Mặt bằng văn phòng đã chọn không tồn tại.',
            'budget_max.gte' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.',
            'preferred_site_tour_at.after_or_equal' => 'Thời gian muốn khảo sát thực tế phải cách thời điểm gửi yêu cầu ít nhất 3 ngày (từ ngày ' . now()->addDays(3)->format('d/m/Y H:i') . ' trở đi).',
        ];
    }
}
