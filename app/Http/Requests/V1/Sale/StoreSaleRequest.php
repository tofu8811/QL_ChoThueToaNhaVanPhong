<?php

namespace App\Http\Requests\V1\Sale;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\HasStaffRules;

class StoreSaleRequest extends BaseApiRequest
{
    use HasStaffRules;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->staffAccountRules('sales', null, false, true, false),
            [
                'commission_rate'      => ['nullable', 'numeric', 'min:0', 'max:100'],
                'max_concurrent_leads' => ['nullable', 'integer', 'min:1'],
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->staffAccountMessages('nhân viên Sales');
    }
}
