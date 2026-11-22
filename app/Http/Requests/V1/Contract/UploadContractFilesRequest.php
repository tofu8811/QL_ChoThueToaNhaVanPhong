<?php

namespace App\Http\Requests\V1\Contract;

use App\Http\Requests\V1\BaseApiRequest;

class UploadContractFilesRequest extends BaseApiRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            'document_type' => ['nullable', 'string', 'in:SIGNED_CONTRACT_SCAN,APPENDIX,HANDOVER_MINUTES,PAYMENT_RECEIPT,OTHER'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Danh sách tệp tin đính kèm là bắt buộc.',
            'files.*.mimes' => 'Định dạng file không hợp lệ (chỉ chấp nhận PDF, JPG, PNG).',
            'files.*.max' => 'Kích thước mỗi file không được vượt quá 20MB.',
        ];
    }
}
