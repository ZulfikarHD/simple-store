<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * StoreSettingSeeder untuk mengisi data default store settings
 * dengan konfigurasi awal toko yang dapat diubah melalui admin panel
 */
class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi default values untuk store settings, antara lain:
     * - Informasi umum toko (nama, alamat, telepon)
     * - Konfigurasi WhatsApp bisnis
     * - Jam operasional toko
     * - Pengaturan delivery
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'store_name',
                'value' => 'Simple Store',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'store_tagline',
                'value' => 'Premium Quality Products',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'store_logo',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
            ],
            [
                'key' => 'store_address',
                'value' => 'Jl. Contoh No. 123, Jakarta',
                'type' => 'text',
                'group' => 'general',
            ],
            [
                'key' => 'store_phone',
                'value' => '021-1234567',
                'type' => 'string',
                'group' => 'general',
            ],

            // WhatsApp Settings
            [
                'key' => 'whatsapp_number',
                'value' => '6281234567890',
                'type' => 'string',
                'group' => 'whatsapp',
            ],
            [
                'key' => 'phone_country_code',
                'value' => 'ID',
                'type' => 'string',
                'group' => 'whatsapp',
            ],

            // Operating Hours - JSON format
            [
                'key' => 'operating_hours',
                'value' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                    'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                    'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                    'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                    'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                    'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                    'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
                ]),
                'type' => 'json',
                'group' => 'hours',
            ],

            // Delivery Settings
            [
                'key' => 'delivery_areas',
                'value' => json_encode([
                    'Jakarta Selatan',
                    'Jakarta Pusat',
                    'Jakarta Timur',
                ]),
                'type' => 'json',
                'group' => 'delivery',
            ],
            [
                'key' => 'delivery_fee',
                'value' => '10000',
                'type' => 'integer',
                'group' => 'delivery',
            ],
            [
                'key' => 'minimum_order',
                'value' => '50000',
                'type' => 'integer',
                'group' => 'delivery',
            ],

            // Order Settings - Auto Cancel
            [
                'key' => 'auto_cancel_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'orders',
            ],
            [
                'key' => 'auto_cancel_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'orders',
            ],

            // WhatsApp Message Templates
            [
                'key' => 'whatsapp_template_confirmed',
                'value' => "Halo *{customer_name}*! 👋\n\nPesanan Anda dengan nomor *#{order_number}* telah *DIKONFIRMASI*. ✅\n\nTotal: *{total}*\n\nPesanan sedang kami proses. Terima kasih telah berbelanja di {store_name}! 🙏",
                'type' => 'text',
                'group' => 'whatsapp_templates',
            ],
            [
                'key' => 'whatsapp_template_preparing',
                'value' => "Halo *{customer_name}*! 👋\n\nPesanan *#{order_number}* sedang *DIPROSES*. 🔄\n\nMohon tunggu sebentar ya. Terima kasih! 🙏",
                'type' => 'text',
                'group' => 'whatsapp_templates',
            ],
            [
                'key' => 'whatsapp_template_ready',
                'value' => "Halo *{customer_name}*! 👋\n\nPesanan *#{order_number}* sudah *SIAP*! 🎉\n\nSilakan ambil pesanan Anda atau tunggu pengiriman. Terima kasih! 🙏",
                'type' => 'text',
                'group' => 'whatsapp_templates',
            ],
            [
                'key' => 'whatsapp_template_delivered',
                'value' => "Halo *{customer_name}*! 👋\n\nPesanan *#{order_number}* telah *DIKIRIM/SELESAI*. ✅\n\nTerima kasih telah berbelanja di {store_name}! Semoga puas dengan pesanan Anda. 🙏",
                'type' => 'text',
                'group' => 'whatsapp_templates',
            ],
            [
                'key' => 'whatsapp_template_cancelled',
                'value' => "Halo *{customer_name}*,\n\nMohon maaf, pesanan *#{order_number}* telah *DIBATALKAN*. ❌\n\n{cancellation_reason}\n\nSilakan hubungi kami jika ada pertanyaan. Terima kasih. 🙏",
                'type' => 'text',
                'group' => 'whatsapp_templates',
            ],

            // Timeline Icons
            [
                'key' => 'timeline_icons',
                'value' => json_encode([
                    'created' => 'Clock',
                    'pending' => 'Clock',
                    'confirmed' => 'CheckCircle2',
                    'preparing' => 'ChefHat',
                    'ready' => 'Package',
                    'delivered' => 'Truck',
                    'cancelled' => 'XCircle',
                ]),
                'type' => 'json',
                'group' => 'appearance',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('store_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
