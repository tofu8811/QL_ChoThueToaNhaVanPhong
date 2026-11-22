<?php

namespace App\Http\Requests\V1\Lead;

use App\Http\Requests\V1\BaseApiRequest;
use App\Rules\VietnamesePhoneNumber;

class StoreCrmLeadRequest extends BaseApiRequest
{
    /**
     * Chuẩn hóa số điện thoại và ánh xạ các alias trường dữ liệu trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        if (!$this->has('contact_full_name') && $this->has('contact_name')) {
            $merge['contact_full_name'] = $this->contact_name;
        }
        if (!$this->has('phone') && $this->has('contact_phone')) {
            $merge['phone'] = $this->contact_phone;
        }
        if (!$this->has('email') && $this->has('contact_email')) {
            $merge['email'] = $this->contact_email;
        }
        if (!$this->filled('email') && ($this->filled('phone') || isset($merge['phone']))) {
            $p = $this->phone ?? $merge['phone'];
            $cleanPhone = preg_replace('/[^0-9]/', '', (string) $p);
            $merge['email'] = $cleanPhone . '@crmlead.local';
        }
        if (!$this->has('desired_area_sqm') && $this->has('expected_area')) {
            $merge['desired_area_sqm'] = $this->expected_area;
        }
        if (!$this->has('budget_max') && $this->has('expected_budget')) {
            $merge['budget_max'] = $this->expected_budget;
        }
        if (!$this->has('customer_note') && $this->has('notes')) {
            $merge['customer_note'] = $this->notes;
        }
        if ($this->lead_source === 'DIRECT_CALL') {
            $merge['lead_source'] = 'HOTLINE';
        } elseif ($this->lead_source === 'WALK_IN') {
            $merge['lead_source'] = 'DIRECT_WALK_IN';
        }
        if ($this->has('phone') || isset($merge['phone'])) {
            $phoneVal = $merge['phone'] ?? $this->phone;
            $merge['phone'] = preg_replace('/[^0-9]/', '', (string) $phoneVal);
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Quy tắc xác thực khi tạo Lead thủ công từ CRM.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_full_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['required', 'string', new VietnamesePhoneNumber],
            'email' => ['required', 'email', 'max:150'],
            'lead_source' => ['nullable', 'string', 'in:WEBSITE,HOTLINE,DIRECT_WALK_IN,BROKER,FACEBOOK_ADS,GOOGLE_ADS,REFERRAL'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'unit_id' => ['nullable', 'uuid', 'exists:units,id'],
            'desired_area_sqm' => ['nullable', 'numeric', 'min:1'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => [
                'nullable',
                'numeric',
                'min:0',
                $this->filled('budget_min') ? 'gte:budget_min' : null,
            ],
            'preferred_site_tour_at' => ['nullable', 'date'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'assigned_sales_id' => ['nullable', 'uuid', 'exists:sales,id'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tùy chỉnh.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'contact_full_name.required' => 'Họ và tên người liên hệ là bắt buộc.',
            'phone.required' => 'Số điện thoại liên hệ là bắt buộc.',
            'email.required' => 'Địa chỉ email là bắt buộc.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'budget_max.gte' => 'Ngân sách tối đa phải lớn hơn hoặc bằng ngân sách tối thiểu.',
        ];
    }
}
