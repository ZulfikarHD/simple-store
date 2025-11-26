<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi form checkout
 * dengan rules untuk customer name, phone, address, dan notes
 * serta custom messages dalam bahasa Indonesia
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'customer_phone' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
            'customer_address' => ['required', 'string', 'min:10', 'max:500'],
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
            'customer_name.required' => 'Nama lengkap harus diisi.',
            'customer_name.string' => 'Nama harus berupa teks.',
            'customer_name.min' => 'Nama minimal 3 karakter.',
            'customer_name.max' => 'Nama maksimal 100 karakter.',

            'customer_phone.required' => 'Nomor telepon harus diisi.',
            'customer_phone.string' => 'Nomor telepon harus berupa teks.',
            'customer_phone.min' => 'Nomor telepon minimal 10 digit.',
            'customer_phone.max' => 'Nomor telepon maksimal 15 digit.',
            'customer_phone.regex' => 'Format nomor telepon tidak valid. Gunakan angka, +, atau -.',

            'customer_address.required' => 'Alamat pengiriman harus diisi.',
            'customer_address.string' => 'Alamat harus berupa teks.',
            'customer_address.min' => 'Alamat terlalu pendek, minimal 10 karakter.',
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
            'customer_name' => 'nama lengkap',
            'customer_phone' => 'nomor telepon',
            'customer_address' => 'alamat pengiriman',
            'notes' => 'catatan',
        ];
    }
}
