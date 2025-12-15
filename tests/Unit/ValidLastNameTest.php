<?php

namespace Tests\Unit;

use App\Rules\ValidPersonName;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Unit test untuk ValidPersonName validation rule
 * Memastikan validasi nama (first name dan last name) tidak mengandung title/gelar dan simbol
 *
 * @author Zulfikar Hidayatullah
 */
class ValidLastNameTest extends TestCase
{
    /**
     * Test nama belakang valid dengan huruf saja
     */
    public function test_valid_last_name_with_letters_only(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Hidayatullah'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * Test nama belakang valid dengan spasi
     */
    public function test_valid_last_name_with_space(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Van Der Berg'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * Test nama belakang valid dengan apostrof (O'Connor)
     */
    public function test_valid_last_name_with_apostrophe(): void
    {
        $validator = Validator::make(
            ['last_name' => "O'Connor"],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertTrue($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan simbol dash
     */
    public function test_fails_with_dash_symbol(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Doe-Smith'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    /**
     * Test nama belakang gagal dengan simbol underscore
     */
    public function test_fails_with_underscore_symbol(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Doe_Smith'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan simbol @
     */
    public function test_fails_with_at_symbol(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Doe@Smith'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan angka
     */
    public function test_fails_with_numbers(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Doe123'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Pak"
     */
    public function test_fails_with_title_pak(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Pak'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Bu"
     */
    public function test_fails_with_title_bu(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Bu'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Mr"
     */
    public function test_fails_with_title_mr(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Mr'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Mrs"
     */
    public function test_fails_with_title_mrs(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Mrs'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Sir"
     */
    public function test_fails_with_title_sir(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Sir'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Mas"
     */
    public function test_fails_with_title_mas(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Mas'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title "Mbak"
     */
    public function test_fails_with_title_mbak(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Mbak'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title diikuti nama (Mr. John)
     */
    public function test_fails_with_title_followed_by_name(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Mr. John'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang gagal dengan title Pak diikuti nama
     */
    public function test_fails_with_pak_followed_by_name(): void
    {
        $validator = Validator::make(
            ['last_name' => 'Pak Andi'],
            ['last_name' => [new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang kosong gagal validasi dengan required rule
     * Note: ValidLastName rule tidak dijalankan jika nilai kosong (Laravel behavior)
     * karena itu perlu kombinasi dengan 'required' rule
     */
    public function test_fails_when_empty_with_required_rule(): void
    {
        $validator = Validator::make(
            ['last_name' => ''],
            ['last_name' => ['required', new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    /**
     * Test nama belakang hanya spasi gagal validasi dengan required rule
     */
    public function test_fails_when_only_spaces_with_required_rule(): void
    {
        $validator = Validator::make(
            ['last_name' => '   '],
            ['last_name' => ['required', new ValidPersonName]]
        );

        $this->assertFalse($validator->passes());
    }

    /**
     * Test nama belakang case insensitive untuk title
     */
    public function test_title_check_is_case_insensitive(): void
    {
        $titles = ['PAK', 'pak', 'Pak', 'MR', 'mr', 'Mr', 'SIR', 'sir', 'Sir'];

        foreach ($titles as $title) {
            $validator = Validator::make(
                ['last_name' => $title],
                ['last_name' => [new ValidPersonName]]
            );

            $this->assertFalse($validator->passes(), "Title '{$title}' should fail validation");
        }
    }

    /**
     * Test nama yang mirip title tapi bukan title harus valid
     * Contoh: "Pakuan" bukan "Pak"
     */
    public function test_valid_names_similar_to_titles(): void
    {
        $validNames = ['Pakuan', 'Bulan', 'Marshall', 'Siregar', 'Masruri', 'Mbappe'];

        foreach ($validNames as $name) {
            $validator = Validator::make(
                ['last_name' => $name],
                ['last_name' => [new ValidPersonName]]
            );

            $this->assertTrue($validator->passes(), "Name '{$name}' should pass validation");
        }
    }
}
