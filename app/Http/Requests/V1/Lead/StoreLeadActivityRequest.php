<?php

namespace App\Http\Requests\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadActivityRequest extends FormRequest
{
    /**
     * Xác định quyền ghi nhật ký hoạt động.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực thêm nhật ký hoạt động tương tác chăm sóc Lead.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'string', 'in:CALL,SITE_TOUR,EMAIL,MEETING,NOTE,QUOTATION_SENT'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'event_at' => ['nullable', 'date'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
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
            'activity_type.required' => 'Loại hoạt động tương tác là bắt buộc.',
            'activity_type.in' => 'Loại hoạt động phải là một trong các giá trị: CALL, SITE_TOUR, EMAIL, MEETING, NOTE, QUOTATION_SENT.',
            'title.required' => 'Tiêu đề tóm tắt hoạt động là bắt buộc.',
        ];
    }
}
