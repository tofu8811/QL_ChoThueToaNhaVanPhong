<?php

namespace App\Http\Requests\V1\Ticket;

use App\Http\Requests\V1\BaseApiRequest;

class AssignTicketRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'assigned_pm_id'         => ['required', 'string', 'uuid', 'exists:property_managers,id'],
            'expected_completion_at' => ['nullable', 'date'],
            'note'                   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_pm_id.required' => 'Vui lòng chọn nhân viên kỹ thuật / BQL phụ trách.',
            'assigned_pm_id.exists'   => 'Nhân viên BQL được chỉ định không tồn tại.',
            'expected_completion_at.date' => 'Thời gian cam kết khắc phục (SLA) không đúng định dạng ngày giờ.',
        ];
    }
}
