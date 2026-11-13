<?php

namespace App\Http\Requests\V1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'             => ['required', 'string', 'in:ON_HOLD,RESOLVED,WAITING_CONFIRMATION,IN_PROGRESS'],
            'on_hold_reason'     => ['required_if:status,ON_HOLD', 'nullable', 'string', 'max:500'],
            'resolution_summary' => ['required_if:status,RESOLVED,WAITING_CONFIRMATION', 'nullable', 'string'],
            'note'               => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'                 => 'Trạng thái xử lý cập nhật là bắt buộc.',
            'status.in'                       => 'Trạng thái cập nhật không hợp lệ (ON_HOLD, RESOLVED, WAITING_CONFIRMATION, IN_PROGRESS).',
            'on_hold_reason.required_if'      => 'Vui lòng cung cấp lý do tạm ngưng khi chuyển sang ON_HOLD.',
            'resolution_summary.required_if'  => 'Vui lòng nhập tóm tắt phương án xử lý / biên bản nghiệm thu kỹ thuật.',
        ];
    }
}
