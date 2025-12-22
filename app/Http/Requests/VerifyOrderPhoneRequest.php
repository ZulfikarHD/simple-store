<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation untuk verifikasi nomor telepon customer
 * dengan format Indonesia dan normalisasi input
 */
class VerifyOrderPhoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Semua user boleh akses untuk verifikasi phone
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare input untuk validation dengan normalisasi phone number
     * menghapus spasi, strip, dan karakter non-digit kecuali +
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('customer_phone')) {
            $phone = $this->customer_phone;

            // Normalize: hapus spasi dan strip
            $phone = preg_replace('/[\s\-]/', '', $phone);

            // Convert format 62xxx ke +62xxx
            if (str_starts_with($phone, '62') && ! str_starts_with($phone, '+')) {
                $phone = '+'.$phone;
            }

            // Convert format 08xxx ke +628xxx
            if (str_starts_with($phone, '0')) {
                $phone = '+62'.substr($phone, 1);
            }

            $this->merge([
                'customer_phone' => $phone,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * Validasi format nomor telepon Indonesia dengan regex
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_phone' => [
                'required',
                'string',
                'regex:/^(\+62|62|0)[0-9]{9,13}$/',
            ],
        ];
    }

    /**
     * Get custom error messages untuk validation
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_phone.required' => 'Nomor telepon wajib diisi.',
            'customer_phone.regex' => 'Format nomor telepon tidak valid. Gunakan format Indonesia: 08xxxxxxxxxx atau +62xxxxxxxxxx',
        ];
    }
}
