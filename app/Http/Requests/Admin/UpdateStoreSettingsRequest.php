<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateStoreSettingsRequest untuk validasi form pengaturan toko
 * dengan rules untuk semua setting fields yang tersedia
 */
class UpdateStoreSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // General Settings
            'store_name' => ['required', 'string', 'max:255'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'store_logo' => ['nullable', 'string', 'max:500'],
            'store_favicon' => ['nullable', 'string', 'max:500'],
            'store_address' => ['nullable', 'string', 'max:1000'],
            'store_phone' => ['nullable', 'string', 'max:50'],

            // WhatsApp Settings
            'whatsapp_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]+$/', // Hanya digit
                function ($attribute, $value, $fail) {
                    $countryCode = $this->input('phone_country_code', 'ID');
                    $cleanPhone = preg_replace('/\D/', '', $value);

                    // Mapping dial codes
                    $dialCodes = [
                        'ID' => '62',
                        'MY' => '60',
                        'SG' => '65',
                        'PH' => '63',
                        'TH' => '66',
                        'VN' => '84',
                        'US' => '1',
                        'AU' => '61',
                    ];

                    // Mapping local prefixes
                    $localPrefixes = [
                        'ID' => '0',
                        'MY' => '0',
                        'SG' => '',
                        'PH' => '0',
                        'TH' => '0',
                        'VN' => '0',
                        'US' => '',
                        'AU' => '0',
                    ];

                    $dialCode = $dialCodes[$countryCode] ?? '62';
                    $localPrefix = $localPrefixes[$countryCode] ?? '0';

                    // Validasi: harus dimulai dengan dial code atau local prefix
                    $startsWithDialCode = str_starts_with($cleanPhone, $dialCode);
                    $startsWithLocalPrefix = $localPrefix && str_starts_with($cleanPhone, $localPrefix);

                    if (! $startsWithDialCode && ! $startsWithLocalPrefix) {
                        $hint = $localPrefix
                            ? "0 atau kode negara {$dialCode}"
                            : "kode negara {$dialCode}";
                        $fail("Nomor WhatsApp harus dimulai dengan {$hint}.");
                    }

                    // Validasi minimal length
                    if (strlen($cleanPhone) < 8) {
                        $fail('Nomor WhatsApp terlalu pendek (minimal 8 digit).');
                    }
                },
            ],
            'phone_country_code' => ['required', 'string', 'in:ID,MY,SG,PH,TH,VN,US,AU'],

            // Operating Hours - JSON format
            'operating_hours' => ['required', 'array'],
            'operating_hours.monday' => ['required', 'array'],
            'operating_hours.monday.open' => ['required', 'string'],
            'operating_hours.monday.close' => ['required', 'string'],
            'operating_hours.monday.is_open' => ['required', 'boolean'],
            'operating_hours.tuesday' => ['required', 'array'],
            'operating_hours.tuesday.open' => ['required', 'string'],
            'operating_hours.tuesday.close' => ['required', 'string'],
            'operating_hours.tuesday.is_open' => ['required', 'boolean'],
            'operating_hours.wednesday' => ['required', 'array'],
            'operating_hours.wednesday.open' => ['required', 'string'],
            'operating_hours.wednesday.close' => ['required', 'string'],
            'operating_hours.wednesday.is_open' => ['required', 'boolean'],
            'operating_hours.thursday' => ['required', 'array'],
            'operating_hours.thursday.open' => ['required', 'string'],
            'operating_hours.thursday.close' => ['required', 'string'],
            'operating_hours.thursday.is_open' => ['required', 'boolean'],
            'operating_hours.friday' => ['required', 'array'],
            'operating_hours.friday.open' => ['required', 'string'],
            'operating_hours.friday.close' => ['required', 'string'],
            'operating_hours.friday.is_open' => ['required', 'boolean'],
            'operating_hours.saturday' => ['required', 'array'],
            'operating_hours.saturday.open' => ['required', 'string'],
            'operating_hours.saturday.close' => ['required', 'string'],
            'operating_hours.saturday.is_open' => ['required', 'boolean'],
            'operating_hours.sunday' => ['required', 'array'],
            'operating_hours.sunday.open' => ['required', 'string'],
            'operating_hours.sunday.close' => ['required', 'string'],
            'operating_hours.sunday.is_open' => ['required', 'boolean'],

            // Delivery Settings
            'delivery_areas' => ['nullable', 'array'],
            'delivery_areas.*' => ['string', 'max:255'],
            'delivery_fee' => ['required', 'integer', 'min:0'],
            'minimum_order' => ['required', 'integer', 'min:0'],

            // Order Settings - Auto Cancel
            'auto_cancel_enabled' => ['required', 'boolean'],
            'auto_cancel_minutes' => ['required', 'integer', 'min:5', 'max:1440'],

            // WhatsApp Message Templates
            // SECURITY: Validasi untuk mencegah template injection
            'whatsapp_template_confirmed' => ['nullable', 'string', 'max:2000', $this->templateSafetyRule()],
            'whatsapp_template_preparing' => ['nullable', 'string', 'max:2000', $this->templateSafetyRule()],
            'whatsapp_template_ready' => ['nullable', 'string', 'max:2000', $this->templateSafetyRule()],
            'whatsapp_template_delivered' => ['nullable', 'string', 'max:2000', $this->templateSafetyRule()],
            'whatsapp_template_cancelled' => ['nullable', 'string', 'max:2000', $this->templateSafetyRule()],

            // Timeline Icons
            // SECURITY: Validasi icon names against whitelist (A03:2021 - Injection)
            'timeline_icons' => ['nullable', 'array'],
            'timeline_icons.created' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
            'timeline_icons.pending' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
            'timeline_icons.confirmed' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
            'timeline_icons.preparing' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
            'timeline_icons.ready' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
            'timeline_icons.delivered' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
            'timeline_icons.cancelled' => ['nullable', 'string', 'max:50', $this->allowedIconsRule()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'store_name.required' => 'Nama toko wajib diisi.',
            'store_name.max' => 'Nama toko maksimal 255 karakter.',
            'store_tagline.max' => 'Tagline toko maksimal 255 karakter.',
            'store_logo.max' => 'Path logo toko maksimal 500 karakter.',
            'store_favicon.max' => 'Path favicon toko maksimal 500 karakter.',
            'store_address.max' => 'Alamat toko maksimal 1000 karakter.',
            'store_phone.max' => 'Nomor telepon maksimal 50 karakter.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
            'phone_country_code.required' => 'Negara/region wajib dipilih.',
            'phone_country_code.in' => 'Negara/region yang dipilih tidak valid.',
            'operating_hours.required' => 'Jam operasional wajib diisi.',
            'delivery_fee.required' => 'Biaya pengiriman wajib diisi.',
            'delivery_fee.integer' => 'Biaya pengiriman harus berupa angka.',
            'delivery_fee.min' => 'Biaya pengiriman tidak boleh negatif.',
            'minimum_order.required' => 'Minimum order wajib diisi.',
            'minimum_order.integer' => 'Minimum order harus berupa angka.',
            'minimum_order.min' => 'Minimum order tidak boleh negatif.',
            'auto_cancel_enabled.required' => 'Status auto-cancel wajib diisi.',
            'auto_cancel_enabled.boolean' => 'Status auto-cancel harus berupa boolean.',
            'auto_cancel_minutes.required' => 'Durasi auto-cancel wajib diisi.',
            'auto_cancel_minutes.integer' => 'Durasi auto-cancel harus berupa angka.',
            'auto_cancel_minutes.min' => 'Durasi auto-cancel minimal 5 menit.',
            'auto_cancel_minutes.max' => 'Durasi auto-cancel maksimal 1440 menit (24 jam).',

            // WhatsApp Templates
            'whatsapp_template_confirmed.max' => 'Template konfirmasi maksimal 2000 karakter.',
            'whatsapp_template_preparing.max' => 'Template diproses maksimal 2000 karakter.',
            'whatsapp_template_ready.max' => 'Template siap maksimal 2000 karakter.',
            'whatsapp_template_delivered.max' => 'Template dikirim maksimal 2000 karakter.',
            'whatsapp_template_cancelled.max' => 'Template dibatalkan maksimal 2000 karakter.',

            // Timeline Icons
            'timeline_icons.*.max' => 'Nama icon maksimal 50 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'store_name' => 'nama toko',
            'store_tagline' => 'tagline toko',
            'store_logo' => 'logo toko',
            'store_favicon' => 'favicon toko',
            'store_address' => 'alamat toko',
            'store_phone' => 'nomor telepon toko',
            'whatsapp_number' => 'nomor WhatsApp',
            'phone_country_code' => 'negara/region',
            'operating_hours' => 'jam operasional',
            'delivery_areas' => 'area pengiriman',
            'delivery_fee' => 'biaya pengiriman',
            'minimum_order' => 'minimum order',
            'auto_cancel_enabled' => 'status auto-cancel',
            'auto_cancel_minutes' => 'durasi auto-cancel',

            // WhatsApp Templates
            'whatsapp_template_confirmed' => 'template konfirmasi',
            'whatsapp_template_preparing' => 'template diproses',
            'whatsapp_template_ready' => 'template siap',
            'whatsapp_template_delivered' => 'template dikirim',
            'whatsapp_template_cancelled' => 'template dibatalkan',

            // Timeline Icons
            'timeline_icons' => 'icon timeline',
            'timeline_icons.created' => 'icon pesanan dibuat',
            'timeline_icons.pending' => 'icon pending',
            'timeline_icons.confirmed' => 'icon dikonfirmasi',
            'timeline_icons.preparing' => 'icon diproses',
            'timeline_icons.ready' => 'icon siap',
            'timeline_icons.delivered' => 'icon dikirim',
            'timeline_icons.cancelled' => 'icon dibatalkan',
        ];
    }

    /**
     * SECURITY: A03:2021 - Injection Prevention
     * Whitelist icon names yang diperbolehkan untuk timeline
     * Mencegah component injection dengan hanya mengizinkan icon names yang valid
     *
     * @return \Closure Validation rule closure
     */
    private function allowedIconsRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            // Whitelist of allowed Lucide icon names
            $allowedIcons = [
                'Clock', 'Timer', 'Hourglass', 'CalendarClock',
                'CheckCircle2', 'CircleCheck', 'Check', 'CircleCheckBig', 'BadgeCheck',
                'ChefHat', 'Utensils', 'Flame', 'CookingPot', 'Loader', 'RefreshCw',
                'Package', 'Box', 'Gift', 'Archive', 'PackageCheck',
                'Truck', 'Car', 'Bike', 'Send', 'Navigation',
                'XCircle', 'X', 'Ban', 'CircleX', 'AlertCircle',
                'ShoppingBag', 'ShoppingCart', 'Receipt', 'FileText',
                'Star', 'Heart', 'ThumbsUp',
            ];

            if (! in_array($value, $allowedIcons, true)) {
                $fail('Icon yang dipilih tidak valid. Silakan pilih dari daftar icon yang tersedia.');
            }
        };
    }

    /**
     * SECURITY: A03:2021 - Injection Prevention
     * Validasi template untuk mencegah injection attacks
     * Mendeteksi pola berbahaya seperti script tags, event handlers, dll.
     *
     * @return \Closure Validation rule closure
     */
    private function templateSafetyRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            // Pola berbahaya yang tidak diperbolehkan dalam template
            $dangerousPatterns = [
                '/<script/i',           // Script tags
                '/javascript:/i',       // JavaScript protocol
                '/on\w+\s*=/i',         // Event handlers (onclick, onerror, etc.)
                '/<iframe/i',           // Iframe injection
                '/<object/i',           // Object injection
                '/<embed/i',            // Embed injection
                '/<form/i',             // Form injection
                '/data:/i',             // Data URI scheme
                '/vbscript:/i',         // VBScript protocol
                '/expression\s*\(/i',   // CSS expression
                '/url\s*\(/i',          // CSS url()
                '/@import/i',           // CSS import
                '/<!--/i',              // HTML comments (potential for comment-based attacks)
                '/\x00/',               // Null bytes
            ];

            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    $fail('Template mengandung konten yang tidak diperbolehkan untuk alasan keamanan.');

                    return;
                }
            }

            // Validasi bahwa variabel yang digunakan valid
            $allowedVariables = [
                '{customer_name}',
                '{order_number}',
                '{total}',
                '{store_name}',
                '{cancellation_reason}',
            ];

            // Cari semua variabel dalam template
            preg_match_all('/\{[^}]+\}/', $value, $matches);

            if (! empty($matches[0])) {
                foreach ($matches[0] as $variable) {
                    if (! in_array($variable, $allowedVariables, true)) {
                        $fail("Variabel '{$variable}' tidak valid. Variabel yang tersedia: ".implode(', ', $allowedVariables));

                        return;
                    }
                }
            }
        };
    }
}
