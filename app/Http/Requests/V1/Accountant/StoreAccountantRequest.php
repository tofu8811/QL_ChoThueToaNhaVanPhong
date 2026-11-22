<?php

namespace App\Http\Requests\V1\Accountant;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\HasStaffRules;

class StoreAccountantRequest extends BaseApiRequest
{
    use HasStaffRules;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->staffAccountRules('accountants', null, false, true, false),
            [
                'can_approve_adjustment' => ['nullable', 'boolean'],
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->staffAccountMessages('Kế toán');
    }
}
