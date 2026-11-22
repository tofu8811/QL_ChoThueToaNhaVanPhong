<?php

namespace App\Http\Requests\V1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailSettingRequest extends FormRequest
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
            'mail_mailer'           => ['nullable', 'string', 'in:smtp,sendmail,log'],
            'mail_host'             => ['nullable', 'string', 'max:255'],
            'mail_port'             => ['nullable', 'integer', 'between:1,65535'],
            'mail_encryption'       => ['nullable', 'string', 'in:tls,ssl,none,null'],
            'mail_username'         => ['required', 'string', 'max:255'],
            'mail_password'         => ['nullable', 'string', 'max:255'],
            'mail_from_address'     => ['required', 'string', 'email', 'max:255'],
            'mail_from_name'        => ['required', 'string', 'max:255'],
            'mail_reply_to_address' => ['nullable', 'string', 'email', 'max:255'],
            'mail_reply_to_name'    => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mail_username.required'     => 'Vui lòng nhập tài khoản email đăng nhập (Username).',
            'mail_from_address.required' => 'Vui lòng nhập địa chỉ email người gửi (From Address).',
            'mail_from_address.email'    => 'Địa chỉ email người gửi không đúng định dạng.',
            'mail_from_name.required'    => 'Vui lòng nhập tên người gửi hiển thị (From Name).',
            'mail_reply_to_address.email'=> 'Địa chỉ email phản hồi (Reply-To) không đúng định dạng.',
        ];
    }
}
