<?php

namespace App\Http\Requests\V1\Contract;

use App\Http\Requests\V1\BaseApiRequest;

class StoreContractRequest extends BaseApiRequest
{
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
            'units.required' => 'Hợp đồng bắt buộc phải chỉ định ít nhất một mặt bằng cho thuê.',
            'units.min' => 'Hợp đồng bắt buộc phải có ít nhất một mặt bằng.',
        ];
    }

    /**
     * Xác thực phân quyền địa bàn tòa nhà & khách thuê theo RBAC.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = auth('sanctum')->user() ?? $this->user() ?? auth()->user();

            if ($user instanceof \App\Models\Sale) {
                // 1. Kiểm tra quyền tòa nhà
                $hasBuilding = $user->buildings()->where('buildings.id', $this->input('building_id'))->exists();
                if (!$hasBuilding) {
                    $validator->errors()->add('building_id', 'Chuyên viên Sales không có thẩm quyền lập hợp đồng cho tòa nhà này.');
                }

                // 2. Kiểm tra quyền khách thuê
                $tenant = \App\Models\Tenant::with('convertedFromLead')->find($this->input('tenant_id'));
                if ($tenant) {
                    $isCreator = ($tenant->created_by_sales_id === $user->id);
                    $isLeadAssignee = ($tenant->convertedFromLead && $tenant->convertedFromLead->assigned_sales_id === $user->id);
                    if (!$isCreator && !$isLeadAssignee) {
                        $validator->errors()->add('tenant_id', 'Bạn không có thẩm quyền lập hợp đồng cho khách thuê của nhân sự khác.');
                    }
                }
            }

            // 3. Kiểm tra mặt bằng phải thuộc đúng tòa nhà của hợp đồng
            $units = $this->input('units');
            if (is_array($units) && !empty($this->input('building_id'))) {
                foreach ($units as $idx => $u) {
                    if (!empty($u['unit_id'])) {
                        $unit = \App\Models\Unit::find($u['unit_id']);
                        if ($unit && $unit->building_id !== $this->input('building_id')) {
                            $validator->errors()->add("units.{$idx}.unit_id", "Mặt bằng {$unit->unit_code} không thuộc tòa nhà đã chọn.");
                        }
                    }
                }
            }
        });
    }
}
