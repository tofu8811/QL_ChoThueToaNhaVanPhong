<?php

namespace App\Http\Requests\V1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirmed' => ['required', 'boolean'],
            'feedback'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmed.required' => 'Trạng thái xác nhận nghiệm thu (confirmed: true/false) là bắt buộc.',
            'confirmed.boolean'  => 'Giá trị xác nhận phải là boolean (true hoặc false).',
            'feedback.max'       => 'Ý kiến phản hồi không được vượt quá 500 ký tự.',
        ];
    }
}
