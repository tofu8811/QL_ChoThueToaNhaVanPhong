<?php

namespace App\Http\Requests\V1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class TestMailSettingRequest extends FormRequest
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
            'recipient_email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_email.required' => 'Vui lòng nhập địa chỉ email nhận thư thử nghiệm.',
            'recipient_email.email'    => 'Địa chỉ email nhận thư thử nghiệm không đúng định dạng.',
        ];
    }
}
