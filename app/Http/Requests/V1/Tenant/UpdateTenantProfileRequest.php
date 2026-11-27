<?php

namespace App\Http\Requests\V1\Tenant;

use App\Helpers\PasswordHelper;
use App\Http\Requests\V1\BaseApiRequest;
use App\Rules\VietnamesePhoneNumber;

class UpdateTenantProfileRequest extends BaseApiRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'billing_email' => ['sometimes', 'required', 'email', 'max:150'],
            'contact_phone' => ['sometimes', 'required', 'string', new VietnamesePhoneNumber],
            'password' => ['nullable', 'string', PasswordHelper::strongRule(), 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'billing_email.email' => 'Hộp thư thanh toán không đúng định dạng.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }
}
