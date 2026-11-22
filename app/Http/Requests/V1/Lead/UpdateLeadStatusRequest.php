<?php

namespace App\Http\Requests\V1\Lead;

use App\Enums\LeadStatus;
use App\Http\Requests\V1\BaseApiRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLeadStatusRequest extends BaseApiRequest
{
    /**
     * Chuẩn hóa và ánh xạ dữ liệu trạng thái trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        // 1. Ánh xạ trường status / pipeline_status
        $rawStatus = $this->status ?? $this->pipeline_status;
        if ($rawStatus !== null) {
            $upper = strtoupper(trim((string) $rawStatus));
            if ($upper === 'WIN') {
                $upper = LeadStatus::WON->value;
            } elseif ($upper === 'LOSE' || $upper === 'FAILED') {
                $upper = LeadStatus::LOST->value;
            }
            $merge['status'] = $upper;
        }

        // 2. Ánh xạ alias nếu có truyền tham số thay thế
        $targetStatus = $merge['status'] ?? $this->status ?? $this->pipeline_status;
        if ($targetStatus === LeadStatus::LOST->value || strtoupper((string) $targetStatus) === 'LOST') {
            if (!$this->filled('lost_reason_category')) {
                $cat = $this->lost_category ?? $this->category ?? null;
                if (!empty($cat)) {
                    $merge['lost_reason_category'] = (string) $cat;
                }
            }
            if (!$this->filled('lost_reason_detail')) {
                $detail = $this->lost_reason_detail ?? $this->lost_reason ?? $this->reason ?? $this->note ?? null;
                if (!empty($detail)) {
                    $merge['lost_reason_detail'] = (string) $detail;
                }
            }
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Quy tắc xác thực chuyển đổi trạng thái tiến trình Lead.
     * Bắt buộc có lý do khi chuyển sang LOST.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(LeadStatus::class)],
            'lost_reason_category' => ['required_if:status,' . LeadStatus::LOST->value, 'nullable', 'string', 'max:100'],
            'lost_reason_detail' => ['required_if:status,' . LeadStatus::LOST->value, 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt tùy chỉnh.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái chuyển đổi là bắt buộc.',
            'lost_reason_category.required_if' => 'Bắt buộc phải chọn danh mục lý do khi đóng Lead ở trạng thái Thất bại (LOST).',
            'lost_reason_detail.required_if' => 'Bắt buộc phải nhập chi tiết lý do khách hàng từ chối khi đóng trạng thái Thất bại (LOST).',
        ];
    }
}
