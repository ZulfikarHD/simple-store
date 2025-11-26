<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * StoreSetting model untuk mengelola konfigurasi toko
 * dengan struktur key-value yang fleksibel, yaitu:
 * nama toko, alamat, WhatsApp, jam operasional, dan delivery settings
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StoreSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Mengambil value setting berdasarkan key
     * dengan casting otomatis berdasarkan type
     *
     * @param  mixed  $default  Default value jika key tidak ditemukan
     * @return mixed Value yang sudah di-cast sesuai type
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getCastedValue();
    }

    /**
     * Menyimpan atau mengupdate setting berdasarkan key
     *
     * @param  mixed  $value  Value yang akan disimpan
     * @param  string  $type  Tipe data (string, text, integer, json, boolean)
     * @param  string  $group  Group setting untuk pengelompokan
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): static
    {
        $stringValue = match ($type) {
            'json' => is_string($value) ? $value : json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Mengambil semua settings berdasarkan group
     *
     * @return Collection<string, mixed> Collection dengan key sebagai setting key
     */
    public static function getGroup(string $group): Collection
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn ($setting) => [$setting->key => $setting->getCastedValue()]);
    }

    /**
     * Mengambil semua settings grouped by group name
     *
     * @return Collection<string, Collection> Nested collection per group
     */
    public static function getAllGrouped(): Collection
    {
        return static::all()
            ->groupBy('group')
            ->map(fn ($settings) => $settings->mapWithKeys(
                fn ($setting) => [$setting->key => $setting->getCastedValue()]
            ));
    }

    /**
     * Get value yang sudah di-cast sesuai type
     *
     * @return mixed Value yang sudah di-cast
     */
    public function getCastedValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'boolean' => (bool) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
