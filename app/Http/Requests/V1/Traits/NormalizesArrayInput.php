<?php

namespace App\Http\Requests\V1\Traits;

/**
 * Trait chuẩn hóa các tham số đầu vào dạng mảng trong Request lifecycle (prepareForValidation),
 * cho phép linh hoạt tiếp nhận cả tham số đơn lẻ (singular) và tự động đóng gói thành mảng (plural).
 */
trait NormalizesArrayInput
{
    /**
     * Chuẩn hóa một trường danh sách, hỗ trợ các alias đơn lẻ.
     *
     * @param string $pluralKey Tên trường mảng đích (ví dụ: 'sales_ids', 'accountant_ids')
     * @param array<string> $singularKeys Danh sách các alias đơn lẻ (ví dụ: ['sales_id', 'sale_id'])
     */
    protected function normalizeArrayField(string $pluralKey, array $singularKeys): void
    {
        $existing = $this->input($pluralKey);

        if (empty($existing)) {
            foreach ($singularKeys as $key) {
                $val = $this->input($key);
                if ($val !== null && $val !== '') {
                    $arrayVal = is_array($val) ? $val : [$val];
                    $this->merge([$pluralKey => $arrayVal]);
                    break;
                }
            }
        }
    }
}
