<?php

namespace App\Http\Requests\V1\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UploadLicenseRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Tệp tin Giấy chứng nhận ĐKKD là bắt buộc.',
            'file.mimes' => 'Hệ thống chỉ chấp nhận file dạng PDF hoặc hình ảnh (JPG, PNG).',
            'file.max' => 'Dung lượng tệp tải lên không được vượt quá 10MB.',
        ];
    }
}
