<?php

namespace App\Http\Requests\V1\Accountant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAccountantRequest extends FormRequest
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
        $accountantId = $this->route('id');

        return [
            'full_name'              => ['sometimes', 'required', 'string', 'max:120'],
            'email'                  => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('accountants', 'email')->ignore($accountantId)->whereNull('deleted_at'),
            ],
            'phone'                  => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('accountants', 'phone')->ignore($accountantId)->whereNull('deleted_at'),
            ],
            'password'               => ['sometimes', 'nullable', 'string', Password::min(8)],
            'can_approve_adjustment' => ['sometimes', 'boolean'],
            'status'                 => ['sometimes', 'string', 'in:ACTIVE,LOCKED,PENDING'],
            'building_ids'           => ['sometimes', 'nullable', 'array'],
            'building_ids.*'         => ['string', 'uuid', 'exists:buildings,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required'    => 'Họ và tên nhân viên Kế toán không được để trống.',
            'email.required'        => 'Email không được để trống.',
            'email.unique'          => 'Email này đã tồn tại trong hệ thống.',
            'phone.unique'          => 'Số điện thoại này đã tồn tại trong hệ thống.',
            'password.min'          => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'building_ids.*.exists' => 'Mã tòa nhà được phân công không tồn tại trong hệ thống.',
        ];
    }
}
