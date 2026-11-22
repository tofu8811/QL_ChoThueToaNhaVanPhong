<?php

namespace App\Http\Requests\V1\Ticket;

use App\Http\Requests\V1\BaseApiRequest;

class RateTicketRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Số sao đánh giá (1-5) là bắt buộc.',
            'rating.min'      => 'Đánh giá tối thiểu 1 sao.',
            'rating.max'      => 'Đánh giá tối đa 5 sao.',
            'feedback.max'    => 'Ý kiến đóng góp không được vượt quá 500 ký tự.',
        ];
    }
}
