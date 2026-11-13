<?php

namespace App\Http\Requests\V1\Contract;

use Illuminate\Foundation\Http\FormRequest;

class TerminateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'termination_note' => ['required', 'string', 'max:500'],
            'refund_deposit_amount' => ['required', 'numeric', 'min:0'],
            'penalty_deposit_amount' => ['required', 'numeric', 'min:0'],
            'terminated_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'termination_note.required' => 'Lý do hoặc biên bản thanh lý hợp đồng là bắt buộc.',
            'refund_deposit_amount.required' => 'Số tiền đặt cọc hoàn trả lại cho khách thuê là bắt buộc.',
            'penalty_deposit_amount.required' => 'Số tiền phạt khấu trừ từ tiền đặt cọc là bắt buộc.',
        ];
    }
}
