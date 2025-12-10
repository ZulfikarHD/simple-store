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
            'store_address' => ['nullable', 'string', 'max:1000'],
            'store_phone' => ['nullable', 'string', 'max:50'],

            // WhatsApp Settings
            'whatsapp_number' => ['required', 'string', 'max:20'],

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
            'store_address.max' => 'Alamat toko maksimal 1000 karakter.',
            'store_phone.max' => 'Nomor telepon maksimal 50 karakter.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.max' => 'Nomor WhatsApp maksimal 20 karakter.',
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
            'store_address' => 'alamat toko',
            'store_phone' => 'nomor telepon toko',
            'whatsapp_number' => 'nomor WhatsApp',
            'operating_hours' => 'jam operasional',
            'delivery_areas' => 'area pengiriman',
            'delivery_fee' => 'biaya pengiriman',
            'minimum_order' => 'minimum order',
            'auto_cancel_enabled' => 'status auto-cancel',
            'auto_cancel_minutes' => 'durasi auto-cancel',
        ];
    }
}
