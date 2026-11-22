<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\HasStaffRules;

class StoreAdminRequest extends BaseApiRequest
{
    use HasStaffRules;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->staffAccountRules('admins', null, false, false, true),
            [
                'is_super_admin' => ['nullable', 'boolean'],
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->staffAccountMessages('quản trị viên');
    }
}
