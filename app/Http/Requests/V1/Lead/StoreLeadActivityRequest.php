<?php

namespace App\Http\Requests\V1\Lead;

use App\Http\Requests\V1\BaseApiRequest;

class StoreLeadActivityRequest extends BaseApiRequest
{
    /**
     * Chuẩn hóa dữ liệu trước khi validate.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        // Ánh xạ SITE_VISIT -> SITE_TOUR (đồng bộ với DB Constraint)
        $rawType = strtoupper(trim((string) ($this->activity_type ?? '')));
        if ($rawType === 'SITE_VISIT') {
            $merge['activity_type'] = 'SITE_TOUR';
        }

        // Ánh xạ summary -> title nếu thiếu title
        if (!$this->filled('title')) {
            $title = $this->summary ?? $this->activity_name ?? null;
            if (!empty($title)) {
                $merge['title'] = trim((string) $title);
            } else {
                $merge['title'] = 'Ghi nhận tương tác chăm sóc khách hàng';
            }
        }

        // Ánh xạ interaction_at -> event_at nếu thiếu event_at
        if (!$this->filled('event_at') && $this->filled('interaction_at')) {
            $merge['event_at'] = $this->interaction_at;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Quy tắc xác thực thêm nhật ký hoạt động tương tác chăm sóc Lead.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'string', 'in:CALL,SITE_TOUR,SITE_VISIT,EMAIL,MEETING,NOTE,QUOTATION_SENT'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'event_at' => ['nullable', 'date'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
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
            'activity_type.required' => 'Loại hoạt động tương tác là bắt buộc.',
            'activity_type.in' => 'Loại hoạt động phải là một trong các giá trị: CALL, SITE_TOUR, EMAIL, MEETING, NOTE, QUOTATION_SENT.',
            'title.required' => 'Tiêu đề tóm tắt hoạt động là bắt buộc.',
        ];
    }
}
