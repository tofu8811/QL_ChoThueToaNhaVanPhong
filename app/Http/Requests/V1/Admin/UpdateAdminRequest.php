<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\HasStaffRules;

class UpdateAdminRequest extends BaseApiRequest
{
    use HasStaffRules;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $adminId = $this->getResourceId();

        return array_merge(
            $this->staffAccountRules('admins', $adminId, true, false, true),
            [
                'is_super_admin' => ['sometimes', 'boolean'],
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
