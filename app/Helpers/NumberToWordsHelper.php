<?php

namespace App\Helpers;

class NumberToWordsHelper
{
    private static array $digits = [
        0 => 'không',
        1 => 'một',
        2 => 'hai',
        3 => 'ba',
        4 => 'bốn',
        5 => 'năm',
        6 => 'sáu',
        7 => 'bảy',
        8 => 'tám',
        9 => 'chín',
    ];

    private static array $units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

    /**
     * Chuyển đổi số tiền VNĐ sang chuỗi tiếng Việt có đơn vị "đồng chẵn".
     * Ví dụ: 630000000 => "Sáu trăm ba mươi triệu đồng chẵn"
     */
    public static function toVietnameseWords(float|int|string $number): string
    {
        $number = (string) round((float) $number);
        if ($number === '0' || $number === '') {
            return 'Không đồng chẵn';
        }

        $negative = false;
        if (str_starts_with($number, '-')) {
            $negative = true;
            $number = substr($number, 1);
        }

        // Tách số thành từng nhóm 3 chữ số từ phải sang trái
        $groups = [];
        while (strlen($number) > 0) {
            $groups[] = substr($number, max(0, strlen($number) - 3));
            $number = substr($number, 0, max(0, strlen($number) - 3));
        }

        $words = [];
        $totalGroups = count($groups);

        for ($i = 0; $i < $totalGroups; $i++) {
            $groupVal = (int) $groups[$i];
            if ($groupVal > 0) {
                $groupWord = self::readThreeDigits($groups[$i], $i < $totalGroups - 1);
                $unit = self::$units[$i] ?? '';
                $words[] = trim($groupWord . ' ' . $unit);
            }
        }

        $result = implode(' ', array_reverse($words));
        $result = preg_replace('/\s+/', ' ', trim($result));

        if ($negative) {
            $result = 'âm ' . $result;
        }

        $result .= ' đồng chẵn';

        // Viết hoa chữ cái đầu tiên
        return mb_strtoupper(mb_substr($result, 0, 1)) . mb_substr($result, 1);
    }

    private static function readThreeDigits(string $str, bool $isSubGroup = false): string
    {
        $num = (int) $str;
        $hundreds = (int) floor($num / 100);
        $tens = (int) floor(($num % 100) / 10);
        $ones = $num % 10;

        $res = '';

        if ($hundreds > 0 || $isSubGroup) {
            $res .= self::$digits[$hundreds] . ' trăm ';
        }

        if ($tens > 1) {
            $res .= self::$digits[$tens] . ' mươi ';
            if ($ones === 1) {
                $res .= 'mốt ';
            } elseif ($ones === 4) {
                $res .= 'tư ';
            } elseif ($ones === 5) {
                $res .= 'lăm ';
            } elseif ($ones > 0) {
                $res .= self::$digits[$ones] . ' ';
            }
        } elseif ($tens === 1) {
            $res .= 'mười ';
            if ($ones === 1) {
                $res .= 'một ';
            } elseif ($ones === 5) {
                $res .= 'lăm ';
            } elseif ($ones > 0) {
                $res .= self::$digits[$ones] . ' ';
            }
        } else { // tens === 0
            if ($ones > 0) {
                if ($hundreds > 0 || $isSubGroup) {
                    $res .= 'lẻ ' . self::$digits[$ones] . ' ';
                } else {
                    $res .= self::$digits[$ones] . ' ';
                }
            }
        }

        return trim($res);
    }
}
