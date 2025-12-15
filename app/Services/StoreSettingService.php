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
        'store_favicon' => ['value' => '', 'type' => 'string', 'group' => 'general'],
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

        // WhatsApp Message Templates
        'whatsapp_template_confirmed' => [
            'value' => "Halo *{customer_name}*! 👋\n\nPesanan Anda dengan nomor *#{order_number}* telah *DIKONFIRMASI*. ✅\n\nTotal: *{total}*\n\nPesanan sedang kami proses. Terima kasih telah berbelanja di {store_name}! 🙏",
            'type' => 'text',
            'group' => 'whatsapp_templates',
        ],
        'whatsapp_template_preparing' => [
            'value' => "Halo *{customer_name}*! 👋\n\nPesanan *#{order_number}* sedang *DIPROSES*. 🔄\n\nMohon tunggu sebentar ya. Terima kasih! 🙏",
            'type' => 'text',
            'group' => 'whatsapp_templates',
        ],
        'whatsapp_template_ready' => [
            'value' => "Halo *{customer_name}*! 👋\n\nPesanan *#{order_number}* sudah *SIAP*! 🎉\n\nSilakan ambil pesanan Anda atau tunggu pengiriman. Terima kasih! 🙏",
            'type' => 'text',
            'group' => 'whatsapp_templates',
        ],
        'whatsapp_template_delivered' => [
            'value' => "Halo *{customer_name}*! 👋\n\nPesanan *#{order_number}* telah *DIKIRIM/SELESAI*. ✅\n\nTerima kasih telah berbelanja di {store_name}! Semoga puas dengan pesanan Anda. 🙏",
            'type' => 'text',
            'group' => 'whatsapp_templates',
        ],
        'whatsapp_template_cancelled' => [
            'value' => "Halo *{customer_name}*,\n\nMohon maaf, pesanan *#{order_number}* telah *DIBATALKAN*. ❌\n\n{cancellation_reason}\n\nSilakan hubungi kami jika ada pertanyaan. Terima kasih. 🙏",
            'type' => 'text',
            'group' => 'whatsapp_templates',
        ],

        // Timeline Icons (mapping status to Lucide icon name)
        'timeline_icons' => [
            'value' => [
                'created' => 'Clock',
                'pending' => 'Clock',
                'confirmed' => 'CheckCircle2',
                'preparing' => 'ChefHat',
                'ready' => 'Package',
                'delivered' => 'Truck',
                'cancelled' => 'XCircle',
            ],
            'type' => 'json',
            'group' => 'appearance',
        ],
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
     * @return array{name: string, tagline: string, logo: string|null, favicon: string|null}
     */
    public function getStoreBranding(): array
    {
        return [
            'name' => $this->getSetting('store_name', 'Simple Store'),
            'tagline' => $this->getSetting('store_tagline', 'Premium Quality Products'),
            'logo' => $this->getSetting('store_logo'),
            'favicon' => $this->getSetting('store_favicon'),
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

    /**
     * Mendapatkan WhatsApp template untuk status tertentu
     * dengan fallback ke default template jika tidak ada
     *
     * @param  string  $status  Status pesanan (confirmed, preparing, ready, delivered, cancelled)
     * @return string Template message
     */
    public function getWhatsAppTemplate(string $status): string
    {
        $key = "whatsapp_template_{$status}";

        return $this->getSetting($key, self::DEFAULT_SETTINGS[$key]['value'] ?? '');
    }

    /**
     * Mendapatkan semua WhatsApp templates untuk ditampilkan di settings
     *
     * @return array<string, string> Mapping status ke template
     */
    public function getAllWhatsAppTemplates(): array
    {
        return [
            'confirmed' => $this->getWhatsAppTemplate('confirmed'),
            'preparing' => $this->getWhatsAppTemplate('preparing'),
            'ready' => $this->getWhatsAppTemplate('ready'),
            'delivered' => $this->getWhatsAppTemplate('delivered'),
            'cancelled' => $this->getWhatsAppTemplate('cancelled'),
        ];
    }

    /**
     * Mendapatkan timeline icons mapping
     *
     * @return array<string, string> Mapping status ke icon name
     */
    public function getTimelineIcons(): array
    {
        return $this->getSetting('timeline_icons', self::DEFAULT_SETTINGS['timeline_icons']['value']);
    }

    /**
     * Parse template dengan mengganti variabel dengan data order
     * Variabel yang tersedia: {customer_name}, {order_number}, {total}, {store_name}, {cancellation_reason}
     *
     * SECURITY: Semua user input di-sanitize untuk mencegah injection attacks
     *
     * @param  string  $template  Template dengan variabel placeholder
     * @param  \App\Models\Order  $order  Order object untuk mengambil data
     * @return string Template yang sudah di-parse
     */
    public function parseTemplateVariables(string $template, \App\Models\Order $order): string
    {
        $storeName = $this->getSetting('store_name', 'Toko Kami');

        // SECURITY: Sanitize user-controlled data untuk mencegah injection
        $variables = [
            '{customer_name}' => $this->sanitizeForTemplate($order->customer_name),
            '{order_number}' => $this->sanitizeForTemplate($order->order_number),
            '{total}' => 'Rp '.number_format($order->total, 0, ',', '.'),
            '{store_name}' => $this->sanitizeForTemplate($storeName),
            '{cancellation_reason}' => $order->cancellation_reason
                ? 'Alasan: '.$this->sanitizeForTemplate($order->cancellation_reason)
                : '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Sanitize string untuk digunakan dalam template
     * Mencegah injection attacks dengan menghapus karakter berbahaya
     *
     * SECURITY: A03:2021 - Injection Prevention
     *
     * @param  string|null  $value  Value yang akan di-sanitize
     * @return string Sanitized value
     */
    private function sanitizeForTemplate(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Remove null bytes dan control characters (kecuali newline dan tab)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value) ?? $value;

        // Limit length untuk mencegah buffer overflow
        $value = mb_substr($value, 0, 500);

        // Escape karakter khusus yang bisa digunakan untuk injection
        // Tidak di-HTML escape karena ini untuk WhatsApp text, bukan HTML
        // Tapi tetap escape backslash dan kurung kurawal untuk template safety
        $value = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $value);

        return $value;
    }

    /**
     * Daftar variabel yang tersedia untuk WhatsApp template
     * digunakan untuk menampilkan tombol insert variable di UI
     *
     * @return array<string, string> Mapping variabel ke deskripsi
     */
    public static function getAvailableTemplateVariables(): array
    {
        return [
            '{customer_name}' => 'Nama Customer',
            '{order_number}' => 'Nomor Pesanan',
            '{total}' => 'Total Pesanan',
            '{store_name}' => 'Nama Toko',
            '{cancellation_reason}' => 'Alasan Pembatalan',
        ];
    }

    /**
     * Daftar icon yang tersedia untuk timeline
     * Subset dari Lucide icons yang relevan untuk order status
     *
     * @return array<string, string> Mapping icon name ke label
     */
    public static function getAvailableTimelineIcons(): array
    {
        return [
            // Time related
            'Clock' => 'Jam',
            'Timer' => 'Timer',
            'Hourglass' => 'Jam Pasir',
            'CalendarClock' => 'Kalender Jam',

            // Check/Success
            'CheckCircle2' => 'Centang Lingkaran',
            'CircleCheck' => 'Lingkaran Centang',
            'Check' => 'Centang',
            'CircleCheckBig' => 'Centang Besar',
            'BadgeCheck' => 'Badge Centang',

            // Processing/Cooking
            'ChefHat' => 'Topi Chef',
            'Utensils' => 'Alat Makan',
            'Flame' => 'Api',
            'CookingPot' => 'Panci',
            'Loader' => 'Loading',
            'RefreshCw' => 'Proses',

            // Package/Ready
            'Package' => 'Paket',
            'Box' => 'Kotak',
            'Gift' => 'Hadiah',
            'Archive' => 'Arsip',
            'PackageCheck' => 'Paket Centang',

            // Delivery
            'Truck' => 'Truk',
            'Car' => 'Mobil',
            'Bike' => 'Sepeda',
            'Send' => 'Kirim',
            'Navigation' => 'Navigasi',

            // Cancel/Error
            'XCircle' => 'X Lingkaran',
            'X' => 'X',
            'Ban' => 'Larangan',
            'CircleX' => 'Lingkaran X',
            'AlertCircle' => 'Alert Lingkaran',

            // Other
            'ShoppingBag' => 'Tas Belanja',
            'ShoppingCart' => 'Keranjang',
            'Receipt' => 'Struk',
            'FileText' => 'Dokumen',
            'Star' => 'Bintang',
            'Heart' => 'Hati',
            'ThumbsUp' => 'Jempol',
        ];
    }
}
