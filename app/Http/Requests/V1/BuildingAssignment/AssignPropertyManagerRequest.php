<?php

namespace App\Http\Requests\V1\BuildingAssignment;

use Illuminate\Foundation\Http\FormRequest;

class AssignPropertyManagerRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn bị dữ liệu trước khi validate.
     * Hỗ trợ alias property_manager_id -> pm_id và is_head_manager -> is_primary.
     */
    protected function prepareForValidation(): void
    {
        $pmId = $this->input('pm_id') ?? $this->input('property_manager_id');
        if ($pmId) {
            $this->merge(['pm_id' => $pmId]);
        }

        if ($this->has('is_head_manager') && !$this->has('is_primary')) {
            $this->merge(['is_primary' => filter_var($this->input('is_head_manager'), FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    /**
     * Quy tắc kiểm thực dữ liệu gán Ban quản lý vào tòa nhà.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pm_id' => ['required', 'uuid', 'exists:property_managers,id'],
            'is_primary' => ['nullable', 'boolean'],
            'is_head_manager' => ['nullable', 'boolean'],
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
            'pm_id.required' => 'Vui lòng chọn nhân viên Ban Quản Lý (PM) để phân công.',
            'pm_id.uuid' => 'Mã định danh BQL không đúng định dạng UUID.',
            'pm_id.exists' => 'Nhân viên Ban Quản Lý không tồn tại trong hệ thống.',
            'is_primary.boolean' => 'Cờ Trưởng ban quản lý phải là true hoặc false.',
            'is_head_manager.boolean' => 'Cờ Trưởng ban quản lý phải là true hoặc false.',
        ];
    }
}
