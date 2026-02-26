<?php

namespace App\Helpers;

class NormalizationHelper
{
    /**
     * Normalize phone to E.164 format
     */
    public static function normalizePhone(?string $phone, string $defaultCountry = 'PT'): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return null;
        }

        // If already has country code (+351, 351), return with +
        if (strlen($phone) >= 11 && str_starts_with($phone, '351')) {
            return '+'.$phone;
        }

        // Portuguese number (9 digits starting with 9, 2 or 3)
        if (strlen($phone) === 9 && in_array($phone[0], ['9', '2', '3'])) {
            return '+351'.$phone;
        }

        // Brazilian number (11 digits with area code)
        if (strlen($phone) === 11 && in_array($phone[0], ['1', '2', '3', '4', '5', '6', '7', '8', '9'])) {
            return '+55'.$phone;
        }

        // Brazilian landline (10 digits with area code)
        if (strlen($phone) === 10 && in_array($phone[0], ['1', '2', '3', '4', '5', '6', '7', '8', '9'])) {
            return '+55'.$phone;
        }

        // Default case: add default country code
        $countryCode = $defaultCountry === 'BR' ? '+55' : '+351';

        return $countryCode.$phone;
    }

    /**
     * Normalize email to lowercase and trim
     */
    public static function normalizeEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        return strtolower(trim($email));
    }

    /**
     * Capitalize proper name (first letter of each word uppercase)
     */
    public static function capitalizeName(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        // Trim and remove double spaces
        $name = preg_replace('/\s+/', ' ', trim($name));

        // Capitalize each word, except connectors
        $connectors = ['de', 'da', 'do', 'dos', 'das', 'e', 'of', 'the', 'and'];
        $words = explode(' ', mb_strtolower($name, 'UTF-8'));

        $result = [];
        foreach ($words as $index => $word) {
            if ($index > 0 && in_array($word, $connectors)) {
                $result[] = $word;
            } else {
                $result[] = mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
            }
        }

        return implode(' ', $result);
    }

    /**
     * Clean and normalize cell value
     */
    public static function cleanValue(mixed $value): ?string
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Convert to string
        $value = (string) $value;

        // Remove extra spaces
        $value = trim($value);

        // Remove line breaks
        $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);

        // Remove multiple spaces
        $value = preg_replace('/\s+/', ' ', $value);

        return $value === '' ? null : $value;
    }
}
