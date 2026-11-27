<?php

namespace App\Helpers;

use Illuminate\Validation\Rules\Password;

/**
 * Class PasswordHelper
 *
 * Tiện ích quản lý mật khẩu mặc định và quy tắc độ mạnh mật khẩu dùng chung cho toàn bộ 5 vai trò.
 */
class PasswordHelper
{
    public const DEFAULT_PASSWORD = '123456Aa@';

    /**
     * Trả về mật khẩu mặc định thống nhất khi khởi tạo hoặc cấp lại tài khoản.
     *
     * @param int $length Tham số giữ tương thích ngược
     * @param string $prefix Tham số giữ tương thích ngược
     * @return string
     */
    public static function generateSecurePassword(int $length = 10, string $prefix = 'Tn@'): string
    {
        return self::DEFAULT_PASSWORD;
    }

    /**
     * Quy tắc kiểm tra độ mạnh mật khẩu đồng bộ cho cả 5 vai trò:
     * tối thiểu 8 ký tự, gồm chữ hoa, chữ thường, chữ số và ký tự đặc biệt.
     *
     * @return Password
     */
    public static function strongRule(): Password
    {
        return Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();
    }
}
