<?php

namespace App\Http\Requests\V1\Ticket;

use App\Http\Requests\V1\BaseApiRequest;

class UploadProofImagesRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'proof_type' => ['required', 'string', 'in:BEFORE,AFTER,INCIDENT,COMPLETION,LOG_ATTACHMENT'],
            'caption'    => ['nullable', 'string', 'max:255'],
            'images'     => ['nullable', 'array'],
            'images.*'   => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof_type.required' => 'Loại hình ảnh bằng chứng là bắt buộc (BEFORE, AFTER, INCIDENT, COMPLETION).',
            'proof_type.in'       => 'Loại ảnh không hợp lệ.',
            'caption.max'         => 'Chú thích ảnh không được vượt quá 255 ký tự.',
        ];
    }
}
