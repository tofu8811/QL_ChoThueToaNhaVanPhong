<?php

namespace App\Http\Requests\V1\Ticket;

use App\Http\Requests\V1\BaseApiRequest;

class ConfirmTicketRequest extends BaseApiRequest
{
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
        ];
    }
}
