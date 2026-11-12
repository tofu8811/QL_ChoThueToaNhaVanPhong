<?php

namespace App\Http\Requests\V1\Ticket;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'required', 'string'],
            'category'    => ['sometimes', 'required', 'string', Rule::in(TicketCategory::values())],
            'priority'    => ['sometimes', 'nullable', 'string', Rule::in(TicketPriority::values())],
            'unit_id'     => ['sometimes', 'nullable', 'string', 'uuid', 'exists:units,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Tiêu đề phiếu yêu cầu không được để trống.',
            'title.max'            => 'Tiêu đề không được vượt quá 200 ký tự.',
            'description.required' => 'Mô tả chi tiết sự cố không được để trống.',
            'category.in'          => 'Danh mục sự cố không hợp lệ.',
            'priority.in'          => 'Mức độ ưu tiên không hợp lệ.',
            'unit_id.exists'       => 'Mặt bằng được chọn không tồn tại trong hệ thống.',
        ];
    }
}
