<?php

namespace App\Http\Requests\V1\Ticket;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Http\Requests\V1\BaseApiRequest;
use Illuminate\Validation\Rule;

class StoreTenantTicketRequest extends BaseApiRequest
{
    /**
     * Quy tắc xác thực dữ liệu đầu vào khi tạo mới Ticket.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id'   => ['nullable', 'string', 'uuid', 'exists:tenants,id'],
            'building_id' => ['required', 'string', 'uuid', 'exists:buildings,id'],
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'category'    => ['required', 'string', Rule::in(TicketCategory::values())],
            'priority'    => ['nullable', 'string', Rule::in(TicketPriority::values())],
            'unit_id'     => ['nullable', 'string', 'uuid', 'exists:units,id'],
            'contract_id' => ['nullable', 'string', 'uuid', 'exists:contracts,id'],
            'images'      => ['nullable', 'array'],
            'images.*'    => ['nullable'],
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh bằng tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'building_id.required' => 'Mã tòa nhà xảy ra sự việc là bắt buộc.',
            'building_id.exists'   => 'Tòa nhà được chọn không tồn tại trong hệ thống.',
            'title.required'       => 'Tiêu đề phiếu yêu cầu là bắt buộc.',
            'description.required' => 'Mô tả chi tiết sự cố hiện trường là bắt buộc.',
            'category.required'    => 'Danh mục sự cố là bắt buộc.',
            'category.in'          => 'Danh mục sự cố không hợp lệ.',
            'priority.in'          => 'Mức độ ưu tiên không hợp lệ.',
            'unit_id.exists'       => 'Mặt bằng được chọn không tồn tại trong hệ thống.',
            'contract_id.exists'   => 'Hợp đồng thuê được chọn không tồn tại trong hệ thống.',
        ];
    }
}
