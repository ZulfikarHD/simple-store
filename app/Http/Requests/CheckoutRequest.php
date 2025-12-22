<?php

namespace App\Http\Requests;

use App\Rules\IndonesianPhoneNumber;
use App\Rules\ValidPersonName;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi form checkout
 * dengan rules untuk customer first name, last name, phone, address, dan notes
 * serta custom messages dalam bahasa Indonesia
 *
 * @author Zulfikar Hidayatullah
 */
class CheckoutRequest extends FormRequest
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
     * Validasi mencakup nama depan dan nama belakang terpisah
     * dengan validasi khusus untuk keduanya (tidak boleh title/simbol)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_first_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                new ValidPersonName,
            ],
            'customer_last_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                new ValidPersonName,
            ],
            'customer_phone' => ['required', 'string', new IndonesianPhoneNumber],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_first_name.required' => 'Nama depan harus diisi.',
            'customer_first_name.string' => 'Nama depan harus berupa teks.',
            'customer_first_name.min' => 'Nama depan minimal 2 karakter.',
            'customer_first_name.max' => 'Nama depan maksimal 50 karakter.',
            'customer_first_name.regex' => 'Nama depan hanya boleh mengandung huruf dan spasi.',

            'customer_last_name.required' => 'Nama belakang harus diisi.',
            'customer_last_name.string' => 'Nama belakang harus berupa teks.',
            'customer_last_name.min' => 'Nama belakang minimal 2 karakter.',
            'customer_last_name.max' => 'Nama belakang maksimal 50 karakter.',

            'customer_phone.required' => 'Nomor telepon harus diisi.',
            'customer_phone.string' => 'Nomor telepon harus berupa teks.',
            'customer_phone.min' => 'Nomor telepon minimal 10 digit.',
            'customer_phone.max' => 'Nomor telepon maksimal 15 digit.',
            'customer_phone.regex' => 'Format nomor telepon tidak valid. Gunakan angka, +, atau -.',

            'customer_address.string' => 'Alamat harus berupa teks.',
            'customer_address.max' => 'Alamat terlalu panjang, maksimal 500 karakter.',

            'notes.string' => 'Catatan harus berupa teks.',
            'notes.max' => 'Catatan terlalu panjang, maksimal 500 karakter.',
        ];
    }

    /**
     * Get custom attribute names for validation.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_first_name' => 'nama depan',
            'customer_last_name' => 'nama belakang',
            'customer_phone' => 'nomor telepon',
            'customer_address' => 'alamat pengiriman',
            'notes' => 'catatan',
        ];
    }

    /**
     * Prepare the data for validation.
     * Normalize phone number ke international format sebelum validasi
     */
    protected function prepareForValidation(): void
    {
        if ($this->customer_phone) {
            // Remove spaces, dashes, and other characters
            $phone = preg_replace('/[^0-9+]/', '', $this->customer_phone);

            // Normalize to international format (+62xxx)
            if (str_starts_with($phone, '0')) {
                $phone = '+62'.substr($phone, 1);
            } elseif (str_starts_with($phone, '62')) {
                $phone = '+'.$phone;
            }

            $this->merge(['customer_phone' => $phone]);
        }
    }

    /**
     * Get the validated data with customer_name combined.
     * Menggabungkan first name dan last name menjadi customer_name
     * untuk disimpan ke database
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array<string, mixed>|mixed
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        // Jika requesting specific key, return as-is
        if ($key !== null) {
            if ($key === 'customer_name') {
                return trim($this->customer_first_name).' '.trim($this->customer_last_name);
            }

            return $validated;
        }

        // Gabungkan first_name dan last_name menjadi customer_name
        $validated['customer_name'] = trim($this->customer_first_name).' '.trim($this->customer_last_name);

        // Hapus field yang tidak diperlukan untuk database
        unset($validated['customer_first_name'], $validated['customer_last_name']);

        return $validated;
    }
}
