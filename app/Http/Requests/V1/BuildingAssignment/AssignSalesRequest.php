<?php

namespace App\Http\Requests\V1\BuildingAssignment;

use Illuminate\Foundation\Http\FormRequest;

class AssignSalesRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn bị dữ liệu trước khi validate (hỗ trợ cả sales_id đơn lẻ lẫn sales_ids mảng).
     */
    protected function prepareForValidation(): void
    {
        $salesIds = $this->input('sales_ids');

        if (!$salesIds) {
            $singleId = $this->input('sales_id') ?? $this->input('sale_id');
            if ($singleId) {
                $salesIds = is_array($singleId) ? $singleId : [$singleId];
                $this->merge(['sales_ids' => $salesIds]);
            }
        }
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
            'sales_ids.array' => 'Danh sách Sales phải là một mảng.',
            'sales_ids.min' => 'Danh sách Sales phải có ít nhất một nhân viên.',
            'sales_ids.*.required' => 'Mã định danh Sales không được để trống.',
            'sales_ids.*.uuid' => 'Mã định danh Sales không đúng định dạng UUID.',
            'sales_ids.*.exists' => 'Nhân viên Sales không tồn tại trong hệ thống.',
            'is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false.',
        ];
    }
}
