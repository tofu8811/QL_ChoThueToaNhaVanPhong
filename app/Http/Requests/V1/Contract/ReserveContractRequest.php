<?php

namespace App\Http\Requests\V1\Contract;

class ReserveContractRequest extends StoreContractRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['deposit_amount'] = ['nullable', 'numeric', 'min:0'];
        $rules['booking_deposit_amount'] = ['required', 'numeric', 'min:0'];
        $rules['reservation_expires_at'] = ['required', 'date', 'after:now'];
        $rules['units'] = ['required', 'array', 'min:1'];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'booking_deposit_amount.required' => 'Tiền đặt cọc giữ chỗ tạm thời là bắt buộc.',
            'reservation_expires_at.required' => 'Hạn chót giữ chỗ tạm thời là bắt buộc.',
            'reservation_expires_at.after' => 'Thời hạn giữ chỗ phải lớn hơn thời điểm hiện tại.',
            'units.required' => 'Phải chọn ít nhất một mặt bằng để giữ chỗ.',
        ]);
    }
}
