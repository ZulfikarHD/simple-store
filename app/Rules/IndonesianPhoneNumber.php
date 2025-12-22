<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation Rule untuk format nomor telepon Indonesia
 * dengan support untuk format +62xxx, 62xxx, dan 08xxx
 * serta validasi bahwa nomor adalah mobile/WhatsApp number
 */
class IndonesianPhoneNumber implements ValidationRule
{
    /**
     * Validate Indonesian phone number format
     * dengan normalisasi dan checking untuk mobile numbers
     *
     * @param  string  $attribute  Nama field yang divalidasi
     * @param  mixed  $value  Value yang akan divalidasi
     * @param  Closure  $fail  Closure untuk menampilkan error message
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $value);

        // Check format: +62xxx, 62xxx, or 08xxx
        $isValid = preg_match('/^(\+62|62|0)[0-9]{9,13}$/', $cleaned);

        if (! $isValid) {
            $fail('Format nomor telepon tidak valid. Gunakan format: 08xxxxxxxxxx atau +62xxxxxxxxxx');

            return;
        }

        // Additional validation: check if it's a mobile number (starts with 8)
        // Indonesian mobile numbers start with 08xx or +628xx
        $isMobile = preg_match('/^(\+62|62|0)8[0-9]{8,12}$/', $cleaned);

        if (! $isMobile) {
            $fail('Nomor telepon harus nomor HP/WhatsApp yang aktif (dimulai dengan 08).');

            return;
        }
    }
}
