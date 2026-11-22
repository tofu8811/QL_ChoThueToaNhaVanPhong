<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Class VietnamesePhoneNumber
 *
 * Custom Validation Rule dùng chung toàn hệ thống cho số điện thoại Việt Nam.
 * - Hỗ trợ cả định dạng quốc tế (+84 / 84) tự động chuẩn hóa về đầu 0.
 * - Loại bỏ khoảng trắng, dấu gạch nối, dấu chấm, ngoặc đơn.
 * - Quy chuẩn: Bắt đầu bằng chữ số 0, theo sau bởi 9 hoặc 10 chữ số (tổng cộng 10-11 chữ số).
 * - Phù hợp với tất cả các đầu số di động và cố định hiện hành tại Việt Nam.
 */
class VietnamesePhoneNumber implements ValidationRule
{
    /**
     * Thực thi quy tắc kiểm thực.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nếu giá trị rỗng, quy tắc này nhường quyền kiểm tra bắt buộc cho rule 'required'/'nullable'
        if ($value === null || $value === '') {
            return;
        }

        $raw = trim((string) $value);

        // Chuẩn hóa tiền tố quốc tế +84 hoặc 84 thành 0
        if (str_starts_with($raw, '+84')) {
            $raw = '0' . substr($raw, 3);
        } elseif (str_starts_with($raw, '84') && strlen(preg_replace('/[^0-9]/', '', $raw)) >= 11) {
            $raw = '0' . substr($raw, 2);
        }

        // Làm sạch ký tự không phải số
        $cleanPhone = preg_replace('/[^0-9]/', '', $raw);

        // Hỗ trợ hotline tổng đài doanh nghiệp (1900, 1800 gồm 8-10 chữ số)
        if (preg_match('/^(1900|1800)[0-9]{4,6}$/', $cleanPhone)) {
            return;
        }

        // Regex chuẩn Việt Nam: bắt đầu bằng 0, tổng độ dài 10 hoặc 11 chữ số
        if (! preg_match('/^0[0-9]{9,10}$/', $cleanPhone)) {
            $fail('Vui lòng cung cấp số điện thoại hợp lệ (bắt đầu bằng số 0, gồm 10 hoặc 11 chữ số).');
        }
    }
}
