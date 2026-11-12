<?php

namespace App\Http\Requests\V1\Lead;

use App\Enums\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLeadStatusRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền cập nhật trạng thái Pipeline hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc xác thực chuyển đổi trạng thái tiến trình Lead.
     * Bắt buộc có lý do khi chuyển sang LOST.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(LeadStatus::class)],
            'lost_reason_category' => ['required_if:status,' . LeadStatus::LOST->value, 'nullable', 'string', 'max:100'],
            'lost_reason_detail' => ['required_if:status,' . LeadStatus::LOST->value, 'nullable', 'string', 'max:500'],
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
            'status.required' => 'Trạng thái chuyển đổi là bắt buộc.',
            'lost_reason_category.required_if' => 'Bắt buộc phải chọn danh mục lý do khi đóng Lead ở trạng thái Thất bại (LOST).',
            'lost_reason_detail.required_if' => 'Bắt buộc phải nhập chi tiết lý do khách hàng từ chối khi đóng trạng thái Thất bại (LOST).',
        ];
    }
}
