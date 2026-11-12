<?php

namespace App\Http\Requests\V1\BuildingAssignment;

use Illuminate\Foundation\Http\FormRequest;

class AssignAccountantRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn bị dữ liệu trước khi validate (hỗ trợ cả accountant_id lẫn accountant_ids).
     */
    protected function prepareForValidation(): void
    {
        $accountantIds = $this->input('accountant_ids');

        if (!$accountantIds) {
            $singleId = $this->input('accountant_id');
            if ($singleId) {
                $accountantIds = is_array($singleId) ? $singleId : [$singleId];
                $this->merge(['accountant_ids' => $accountantIds]);
            }
        }
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
            'accountant_ids.array' => 'Danh sách Kế toán phải là một mảng.',
            'accountant_ids.min' => 'Danh sách Kế toán phải có ít nhất một nhân sự.',
            'accountant_ids.*.required' => 'Mã định danh Kế toán không được để trống.',
            'accountant_ids.*.uuid' => 'Mã định danh Kế toán không đúng định dạng UUID.',
            'accountant_ids.*.exists' => 'Nhân viên Kế toán không tồn tại trong hệ thống.',
        ];
    }
}
