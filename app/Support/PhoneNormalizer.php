<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (! str_starts_with($digits, '0') && ! str_starts_with($digits, '00') && ! str_starts_with($digits, '1') && strlen($digits) >= 9) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return '+'.substr($digits, 1);
        }

        return '+'.$digits;
    }
}
