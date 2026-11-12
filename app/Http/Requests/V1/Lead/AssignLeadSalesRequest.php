<?php

namespace App\Http\Requests\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class AssignLeadSalesRequest extends FormRequest
{
    /**
     * Xác định quyền điều phối Sales của người dùng.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực khi chỉ định / điều phối Sales cho Lead.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sales_id' => ['required', 'uuid', 'exists:sales,id'],
            'assignment_note' => ['nullable', 'string', 'max:500'],
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
            'sales_id.required' => 'Chuyên viên kinh doanh được phân công là bắt buộc.',
            'sales_id.exists' => 'Chuyên viên kinh doanh không tồn tại trong hệ thống.',
        ];
    }
}
