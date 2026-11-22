<?php

namespace App\Http\Requests\V1\BuildingAssignment;

use App\Http\Requests\V1\BaseApiRequest;
use App\Http\Requests\V1\Traits\NormalizesArrayInput;

class AssignSalesRequest extends BaseApiRequest
{
    use NormalizesArrayInput;

    /**
     * Chuẩn bị dữ liệu trước khi validate (hỗ trợ cả sales_id đơn lẻ lẫn sales_ids mảng).
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeArrayField('sales_ids', ['sales_id', 'sale_id']);
    }

    /**
     * Quy tắc kiểm thực dữ liệu gán Sales vào tòa nhà.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sales_ids' => ['required', 'array', 'min:1'],
            'sales_ids.*' => ['required', 'uuid', 'exists:sales,id'],
            'is_active' => ['nullable', 'boolean'],
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
            'sales_ids.required' => 'Vui lòng chọn ít nhất một nhân viên Sales để phân công.',
            'sales_ids.min' => 'Danh sách Sales phải có ít nhất một nhân viên.',
            'sales_ids.*.exists' => 'Nhân viên Sales không tồn tại trong hệ thống.',
        ];
    }
}
