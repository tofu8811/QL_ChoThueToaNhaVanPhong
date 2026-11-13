<?php

namespace App\Http\Requests\V1\Contract;

use Illuminate\Foundation\Http\FormRequest;

class RenewContractRequest extends FormRequest
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
            'new_end_date' => ['required', 'date'],
            'annual_price_escalation_percent' => ['nullable', 'numeric', 'min:0'],
            'contract_number' => ['nullable', 'string', 'max:50', 'unique:contracts,contract_number'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_end_date.required' => 'Ngày kết thúc mới cho chu kỳ gia hạn là bắt buộc.',
        ];
    }
}
