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
            'units.*.unit_id' => ['required_with:units', 'uuid', 'distinct', 'exists:units,id'],
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
            'units.required' => 'Hợp đồng bắt buộc phải chọn ít nhất một mặt bằng cho thuê.',
            'units.min' => 'Hợp đồng bắt buộc phải có ít nhất một mặt bằng.',
            'units.*.unit_id.distinct' => 'Mỗi mặt bằng chỉ được thêm vào hợp đồng một lần duy nhất.',
            'units.*.leased_area_sqm.gt' => 'Diện tích thuê của mỗi mặt bằng phải lớn hơn 0 m².',
            'units.*.agreed_rental_price_per_sqm.gt' => 'Đơn giá thuê của mỗi mặt bằng phải lớn hơn 0 VNĐ.',
        ];
    }

    /**
     * Xác thực phân quyền & tính toàn vẹn mặt bằng theo tòa nhà.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = auth('sanctum')->user() ?? $this->user() ?? auth()->user();
            $contractId = $this->route('id') ?? $this->route('contract');
            $contract = $contractId ? \App\Models\Contract::find($contractId) : null;

            if ($contract && $user instanceof \App\Models\Sale) {
                if ($contract->created_by_sales_id !== $user->id) {
                    $validator->errors()->add('authorization', 'Chuyên viên Sales chỉ có thẩm quyền chỉnh sửa hợp đồng do chính mình tạo.');
                }
            }

            // Kiểm tra mặt bằng phải thuộc đúng tòa nhà của hợp đồng
            $units = $this->input('units');
            if ($contract && is_array($units)) {
                foreach ($units as $idx => $u) {
                    if (!empty($u['unit_id'])) {
                        $unit = \App\Models\Unit::find($u['unit_id']);
                        if ($unit && $unit->building_id !== $contract->building_id) {
                            $validator->errors()->add("units.{$idx}.unit_id", "Mặt bằng {$unit->unit_code} không thuộc tòa nhà của hợp đồng.");
                        }
                    }
                }
            }
        });
    }
}
