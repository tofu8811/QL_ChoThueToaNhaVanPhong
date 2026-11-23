<?php

namespace App\Http\Requests\V1\Contract;

use App\Http\Requests\V1\BaseApiRequest;

class UpdateContractRequest extends BaseApiRequest
{
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
            'handover_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'units' => ['sometimes', 'required', 'array', 'min:1'],
            'units.*.unit_id' => ['required_with:units', 'uuid', 'exists:units,id'],
            'units.*.leased_area_sqm' => ['required_with:units', 'numeric', 'gt:0'],
            'units.*.agreed_rental_price_per_sqm' => ['required_with:units', 'numeric', 'gt:0'],
            'units.*.agreed_service_fee_per_sqm' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Ngày bắt đầu tính tiền thuê là bắt buộc.',
            'end_date.required' => 'Ngày kết thúc hợp đồng là bắt buộc.',
            'end_date.after' => 'Ngày kết thúc phải diễn ra sau ngày bắt đầu thuê.',
            'deposit_amount.required' => 'Tiền đặt cọc hợp đồng là bắt buộc.',
            'deposit_amount.min' => 'Tiền đặt cọc không được là số âm.',
            'units.required' => 'Hợp đồng bắt buộc phải chỉ định ít nhất một mặt bằng cho thuê.',
            'units.min' => 'Hợp đồng bắt buộc phải có ít nhất một mặt bằng.',
        ];
    }
}
