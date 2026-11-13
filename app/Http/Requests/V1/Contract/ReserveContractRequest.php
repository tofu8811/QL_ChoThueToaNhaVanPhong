<?php

namespace App\Http\Requests\V1\Contract;

use Illuminate\Foundation\Http\FormRequest;

class ReserveContractRequest extends FormRequest
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
            'contract_number' => ['nullable', 'string', 'max:50', 'unique:contracts,contract_number'],
            'building_id' => ['required', 'uuid', 'exists:buildings,id'],
            'tenant_id' => ['required', 'uuid', 'exists:tenants,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'booking_deposit_amount' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'reservation_expires_at' => ['required', 'date', 'after:now'],
            'payment_cycle_months' => ['nullable', 'integer', 'in:1,3,6,12'],
            'payment_due_day' => ['nullable', 'integer', 'between:1,28'],
            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_id' => ['required', 'uuid', 'exists:units,id'],
            'units.*.leased_area_sqm' => ['required', 'numeric', 'gt:0'],
            'units.*.agreed_rental_price_per_sqm' => ['required', 'numeric', 'gt:0'],
            'units.*.agreed_service_fee_per_sqm' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_deposit_amount.required' => 'Tiền đặt cọc giữ chỗ tạm thời là bắt buộc.',
            'reservation_expires_at.required' => 'Hạn chót giữ chỗ tạm thời là bắt buộc.',
            'reservation_expires_at.after' => 'Thời hạn giữ chỗ phải lớn hơn thời điểm hiện tại.',
            'units.required' => 'Phải chọn ít nhất một mặt bằng để giữ chỗ.',
        ];
    }
}
