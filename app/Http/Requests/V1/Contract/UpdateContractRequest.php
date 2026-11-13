<?php

namespace App\Http\Requests\V1\Contract;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
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
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'deposit_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'booking_deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'reservation_expires_at' => ['nullable', 'date'],
            'payment_cycle_months' => ['nullable', 'integer', 'in:1,3,6,12'],
            'payment_due_day' => ['nullable', 'integer', 'between:1,28'],
            'late_fee_interest_percent' => ['nullable', 'numeric', 'min:0'],
            'annual_price_escalation_percent' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
