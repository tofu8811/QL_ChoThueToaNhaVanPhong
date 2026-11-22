<?php

namespace App\Http\Requests\V1\BuildingAssignment;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\NormalizesArrayInput;

class AssignAccountantRequest extends BaseApiRequest
{
    use NormalizesArrayInput;

    /**
     * Chuẩn bị dữ liệu trước khi validate (hỗ trợ cả accountant_id lẫn accountant_ids).
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeArrayField('accountant_ids', ['accountant_id']);
    }

    /**
     * Quy tắc kiểm thực dữ liệu gán Kế toán vào tòa nhà.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'accountant_ids' => ['required', 'array', 'min:1'],
            'accountant_ids.*' => ['required', 'uuid', 'exists:accountants,id'],
        ];
    }

    /**
     * Thông báo lỗi tùy biến bằng Tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'accountant_ids.required' => 'Vui lòng chọn ít nhất một nhân viên Kế toán để phân công.',
            'accountant_ids.min' => 'Danh sách Kế toán phải có ít nhất một nhân sự.',
            'accountant_ids.*.exists' => 'Nhân viên Kế toán không tồn tại trong hệ thống.',
        ];
    }
}
