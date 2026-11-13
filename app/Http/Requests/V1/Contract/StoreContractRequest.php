<?php

namespace App\Http\Requests\V1\Contract;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
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
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'payment_cycle_months' => ['nullable', 'integer', 'in:1,3,6,12'],
            'payment_due_day' => ['nullable', 'integer', 'between:1,28'],
            'late_fee_interest_percent' => ['nullable', 'numeric', 'min:0'],
            'annual_price_escalation_percent' => ['nullable', 'numeric', 'min:0'],
            'units' => ['nullable', 'array'],
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
            'building_id.required' => 'Tòa nhà cho thuê là bắt buộc.',
            'building_id.exists' => 'Tòa nhà không tồn tại trong hệ thống.',
            'tenant_id.required' => 'Khách thuê doanh nghiệp là bắt buộc.',
            'tenant_id.exists' => 'Khách thuê không tồn tại trong hệ thống.',
            'start_date.required' => 'Ngày bắt đầu tính tiền thuê là bắt buộc.',
            'end_date.required' => 'Ngày kết thúc hợp đồng là bắt buộc.',
            'end_date.after' => 'Ngày kết thúc phải diễn ra sau ngày bắt đầu thuê.',
            'deposit_amount.required' => 'Tiền đặt cọc hợp đồng là bắt buộc.',
            'deposit_amount.min' => 'Tiền đặt cọc không được là số âm.',
        ];
    }
}
