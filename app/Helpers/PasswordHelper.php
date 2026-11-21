<?php

namespace App\Helpers;

/**
 * Class PasswordHelper
 *
 * Tiện ích sinh mật khẩu ngẫu nhiên an toàn theo chuẩn B2B Tenant Portal:
 * - Tiền tố nhận diện thương hiệu: "Tn@"
 * - Kết hợp ngẫu nhiên chữ hoa, chữ thường, chữ số và ký tự đặc biệt
 * - Loại trừ các ký tự dễ gây nhầm lẫn thị giác (0, O, o, 1, l, I)
 * - Đảm bảo thỏa mãn các tiêu chuẩn độ mạnh mật khẩu doanh nghiệp
 */
class PasswordHelper
{
    /**
     * Sinh mật khẩu ngẫu nhiên an toàn cao (mặc định 10 ký tự, ví dụ: Tn@8kM9#2).
     *
     * @param int $length Tổng độ dài mật khẩu (tối thiểu 8, mặc định 10)
     * @param string $prefix Tiền tố nhận diện (mặc định 'Tn@')
     * @return string
     */
    public static function generateSecurePassword(int $length = 10, string $prefix = 'Tn@'): string
    {
        $targetLength = max(8, $length);
        $remainingLength = max(4, $targetLength - strlen($prefix));

        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Loại bỏ I, O
        $lower = 'abcdefghjkmnpqrstuvwxyz'; // Loại bỏ l, o
        $digits = '23456789';               // Loại bỏ 0, 1
        $specials = '#!$%*';

        // Đảm bảo luôn có ít nhất 1 ký tự từ mỗi nhóm
        $chars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        $allPool = $upper . $lower . $digits . $specials;
        while (count($chars) < $remainingLength) {
            $chars[] = $allPool[random_int(0, strlen($allPool) - 1)];
        }

        shuffle($chars);

        return $prefix . implode('', $chars);
    }
}
