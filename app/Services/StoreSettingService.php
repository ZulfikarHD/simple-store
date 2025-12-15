<?php

namespace App\Services;

use App\Models\StoreSetting;
use Illuminate\Support\Collection;

/**
 * StoreSettingService untuk mengelola konfigurasi toko di admin panel
 * dengan fitur bulk update, grouped settings, dan default values
 */
class StoreSettingService
{
    /**
     * Daftar default settings dengan struktur lengkap
     * digunakan untuk inisialisasi dan reset settings
     *
     * @var array<string, array{value: mixed, type: string, group: string}>
     */
    public const DEFAULT_SETTINGS = [
        'store_name' => ['value' => 'Simple Store', 'type' => 'string', 'group' => 'general'],
        'store_tagline' => ['value' => 'Premium Quality Products', 'type' => 'string', 'group' => 'general'],
        'store_logo' => ['value' => '', 'type' => 'string', 'group' => 'general'],
        'store_address' => ['value' => '', 'type' => 'text', 'group' => 'general'],
        'store_phone' => ['value' => '', 'type' => 'string', 'group' => 'general'],
        'whatsapp_number' => ['value' => '', 'type' => 'string', 'group' => 'whatsapp'],
        'phone_country_code' => ['value' => 'ID', 'type' => 'string', 'group' => 'whatsapp'],
        'operating_hours' => [
            'value' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
            ],
            'type' => 'json',
            'group' => 'hours',
        ],
        'delivery_areas' => ['value' => [], 'type' => 'json', 'group' => 'delivery'],
        'delivery_fee' => ['value' => 0, 'type' => 'integer', 'group' => 'delivery'],
        'minimum_order' => ['value' => 0, 'type' => 'integer', 'group' => 'delivery'],
        'auto_cancel_minutes' => ['value' => 30, 'type' => 'integer', 'group' => 'orders'],
        'auto_cancel_enabled' => ['value' => true, 'type' => 'boolean', 'group' => 'orders'],
    ];

    /**
     * Mendapatkan semua settings grouped by category
     * untuk ditampilkan di halaman settings admin
     *
     * @return array<string, mixed> Settings yang sudah dikelompokkan
     */
    public function getAllSettings(): array
    {
        $settings = StoreSetting::all()->keyBy('key');

        $result = [];
        foreach (self::DEFAULT_SETTINGS as $key => $default) {
            $setting = $settings->get($key);
            $result[$key] = $setting ? $setting->getCastedValue() : $default['value'];
        }

        return $result;
    }

    /**
     * Mendapatkan settings yang dikelompokkan berdasarkan group
     * untuk tampilan yang lebih terstruktur di frontend
     *
     * @return Collection<string, Collection> Nested collection per group
     */
    public function getGroupedSettings(): Collection
    {
        return StoreSetting::getAllGrouped();
    }

    /**
     * Mendapatkan single setting value dengan default fallback
     *
     * @param  mixed  $default  Default value jika tidak ditemukan
     * @return mixed Value setting yang sudah di-cast
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $value = StoreSetting::get($key);

        if ($value === null && isset(self::DEFAULT_SETTINGS[$key])) {
            return self::DEFAULT_SETTINGS[$key]['value'];
        }

        return $value ?? $default;
    }

    /**
     * Update multiple settings sekaligus (bulk update)
     * dengan auto-detection type berdasarkan DEFAULT_SETTINGS
     *
     * @param  array<string, mixed>  $data  Data settings dari form request
     * @return array{success: bool, message: string}
     */
    public function updateSettings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (! isset(self::DEFAULT_SETTINGS[$key])) {
                continue;
            }

            $config = self::DEFAULT_SETTINGS[$key];
            StoreSetting::set($key, $value, $config['type'], $config['group']);
        }

        return [
            'success' => true,
            'message' => 'Pengaturan toko berhasil disimpan.',
        ];
    }

    /**
     * Daftar konfigurasi dial code per negara
     * untuk format nomor telepon internasional
     */
    private const COUNTRY_DIAL_CODES = [
        'ID' => ['dialCode' => '62', 'localPrefix' => '0'],
        'MY' => ['dialCode' => '60', 'localPrefix' => '0'],
        'SG' => ['dialCode' => '65', 'localPrefix' => ''],
        'PH' => ['dialCode' => '63', 'localPrefix' => '0'],
        'TH' => ['dialCode' => '66', 'localPrefix' => '0'],
        'VN' => ['dialCode' => '84', 'localPrefix' => '0'],
        'US' => ['dialCode' => '1', 'localPrefix' => ''],
        'AU' => ['dialCode' => '61', 'localPrefix' => '0'],
    ];

    /**
     * Mendapatkan nomor WhatsApp yang sudah di-format dengan country code
     * untuk digunakan di WhatsApp API (customer to owner)
     */
    public function getWhatsAppNumber(): string
    {
        $number = $this->getSetting('whatsapp_number', '');

        return $this->formatPhoneToInternational($number);
    }

    /**
     * Format nomor telepon ke format internasional berdasarkan phone_country_code setting
     * Berguna untuk WhatsApp API yang memerlukan format internasional
     *
     * @param  string  $phone  Nomor telepon dalam format apapun
     * @return string Nomor telepon dalam format internasional (tanpa +)
     */
    public function formatPhoneToInternational(string $phone): string
    {
        // Remove non-numeric characters
        $cleanPhone = preg_replace('/\D/', '', $phone) ?? '';

        // Ambil country code dari settings
        $countryCode = $this->getSetting('phone_country_code', 'ID');
        $config = self::COUNTRY_DIAL_CODES[$countryCode] ?? self::COUNTRY_DIAL_CODES['ID'];

        $dialCode = $config['dialCode'];
        $localPrefix = $config['localPrefix'];

        // Jika sudah dimulai dengan dial code, return langsung
        if (str_starts_with($cleanPhone, $dialCode)) {
            return $cleanPhone;
        }

        // Jika dimulai dengan local prefix (contoh: 0 untuk Indonesia)
        // ganti dengan dial code
        if ($localPrefix && str_starts_with($cleanPhone, $localPrefix)) {
            $cleanPhone = $dialCode.substr($cleanPhone, strlen($localPrefix));
        }
        // Jika tidak ada prefix, tambahkan dial code
        elseif (! str_starts_with($cleanPhone, $dialCode) && strlen($cleanPhone) >= 8) {
            $cleanPhone = $dialCode.$cleanPhone;
        }

        return $cleanPhone;
    }

    /**
     * Mendapatkan phone country code yang dikonfigurasi
     */
    public function getPhoneCountryCode(): string
    {
        return $this->getSetting('phone_country_code', 'ID');
    }

    /**
     * Mendapatkan jam operasional untuk hari tertentu
     *
     * @return array{open: string, close: string, is_open: bool}|null
     */
    public function getOperatingHoursForDay(string $day): ?array
    {
        $hours = $this->getSetting('operating_hours', []);

        return $hours[strtolower($day)] ?? null;
    }

    /**
     * Cek apakah toko sedang buka berdasarkan waktu sekarang
     */
    public function isStoreOpen(): bool
    {
        $dayName = strtolower(now()->format('l'));
        $hours = $this->getOperatingHoursForDay($dayName);

        if (! $hours || ! $hours['is_open']) {
            return false;
        }

        $currentTime = now()->format('H:i');

        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    /**
     * Mendapatkan biaya delivery
     */
    public function getDeliveryFee(): int
    {
        return (int) $this->getSetting('delivery_fee', 0);
    }

    /**
     * Mendapatkan minimum order amount
     */
    public function getMinimumOrder(): int
    {
        return (int) $this->getSetting('minimum_order', 0);
    }

    /**
     * Mendapatkan daftar area pengiriman
     *
     * @return array<int, string>
     */
    public function getDeliveryAreas(): array
    {
        return $this->getSetting('delivery_areas', []);
    }

    /**
     * Mendapatkan data branding toko untuk shared props
     * yang digunakan di seluruh aplikasi
     *
     * @return array{name: string, tagline: string, logo: string|null}
     */
    public function getStoreBranding(): array
    {
        return [
            'name' => $this->getSetting('store_name', 'Simple Store'),
            'tagline' => $this->getSetting('store_tagline', 'Premium Quality Products'),
            'logo' => $this->getSetting('store_logo'),
        ];
    }

    /**
     * Mendapatkan durasi auto-cancel pending orders dalam menit
     */
    public function getAutoCancelMinutes(): int
    {
        return (int) $this->getSetting('auto_cancel_minutes', 30);
    }

    /**
     * Cek apakah auto-cancel pending orders diaktifkan
     */
    public function isAutoCancelEnabled(): bool
    {
        return (bool) $this->getSetting('auto_cancel_enabled', true);
    }
}
