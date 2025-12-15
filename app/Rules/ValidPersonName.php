<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule untuk memvalidasi nama orang (first name atau last name), yaitu:
 * - Tidak boleh menggunakan gelar/title (sir, mr, mrs, pak, bu, mas, mbak, dll)
 * - Tidak boleh mengandung simbol seperti "-", "_", "@", dll
 * - Hanya boleh menggunakan huruf dan spasi
 *
 * @author Zulfikar Hidayatullah
 */
class ValidPersonName implements ValidationRule
{
    /**
     * Daftar gelar/title yang tidak diperbolehkan
     * dalam berbagai bahasa (Indonesia dan Inggris)
     *
     * @var array<string>
     */
    protected array $forbiddenTitles = [
        // English titles
        'sir',
        'mr',
        'mrs',
        'ms',
        'miss',
        'dr',
        'prof',
        'madam',
        'lord',
        'lady',

        // Indonesian titles
        'pak',
        'bu',
        'bapak',
        'ibu',
        'mas',
        'mbak',
        'kak',
        'kakak',
        'bang',
        'abang',
        'tuan',
        'nyonya',
        'nona',
        'haji',
        'hajjah',
        'ustadz',
        'ustadzah',
        'kyai',
        'nyai',
        'raden',
        'drs',
        'drg',
        'ir',
        'sh',
        'se',
        'mm',
        'mba',
    ];

    /**
     * Run the validation rule.
     * Memvalidasi bahwa nama tidak mengandung title/gelar
     * dan tidak menggunakan simbol yang tidak diperbolehkan
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Tentukan label nama berdasarkan attribute
        $nameLabel = $this->getNameLabel($attribute);

        // Validasi nilai tidak boleh kosong
        if (empty(trim($value))) {
            $fail("{$nameLabel} tidak boleh kosong.");

            return;
        }

        $normalizedValue = strtolower(trim($value));

        // Cek apakah mengandung simbol yang tidak diperbolehkan
        // Hanya mengizinkan huruf (termasuk unicode), spasi, dan apostrof untuk nama seperti O'Connor
        if (! preg_match("/^[\p{L}\s']+$/u", $value)) {
            $fail("{$nameLabel} hanya boleh mengandung huruf dan spasi, tidak boleh menggunakan simbol seperti \"-\", \"_\", \"@\", dll.");

            return;
        }

        // Cek apakah nilai adalah title/gelar (exact match)
        if (in_array($normalizedValue, $this->forbiddenTitles, true)) {
            $fail("{$nameLabel} tidak boleh menggunakan gelar atau panggilan seperti Sir, Mr, Mrs, Pak, Bu, Mas, Mbak, dll.");

            return;
        }

        // Cek apakah nilai dimulai dengan title/gelar diikuti spasi atau titik
        foreach ($this->forbiddenTitles as $title) {
            // Cek pattern: "title " atau "title."
            if (preg_match('/^'.preg_quote($title, '/').'[\s.]/i', $normalizedValue)) {
                $fail("{$nameLabel} tidak boleh dimulai dengan gelar atau panggilan seperti Sir, Mr, Mrs, Pak, Bu, Mas, Mbak, dll.");

                return;
            }
        }
    }

    /**
     * Mendapatkan label nama berdasarkan attribute untuk pesan error
     * yang lebih user-friendly
     */
    private function getNameLabel(string $attribute): string
    {
        return match (true) {
            str_contains($attribute, 'first') => 'Nama depan',
            str_contains($attribute, 'last') => 'Nama belakang',
            default => 'Nama',
        };
    }
}
