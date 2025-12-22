<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model Product untuk menyimpan data produk F&B
 * termasuk informasi harga, stok, dan status ketersediaan
 * dengan relasi ke Category dan OrderItem
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image',
        'stock',
        'is_active',
        'is_featured',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Boot method untuk auto-generate slug dari name
     * dan sanitize input untuk mencegah XSS attacks
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && ! $product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Set name attribute dengan sanitization untuk mencegah XSS
     * Strip semua HTML tags dari product name
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = strip_tags($value);
    }

    /**
     * Set description attribute dengan sanitization untuk mencegah XSS
     * Allow hanya safe HTML tags untuk formatting text
     */
    public function setDescriptionAttribute($value): void
    {
        // Allow hanya tags yang aman untuk formatting
        $this->attributes['description'] = strip_tags(
            $value,
            '<p><br><strong><em><ul><ol><li><b><i>'
        );
    }

    /**
     * Mendapatkan kategori dari produk ini
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Mendapatkan semua order items yang mengandung produk ini
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Mendapatkan semua cart items yang mengandung produk ini
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope untuk filter produk yang aktif saja
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Product>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter produk featured
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Product>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope untuk filter produk yang tersedia (in stock)
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Product>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope untuk pencarian produk berdasarkan nama dan deskripsi
     * dengan case-insensitive matching menggunakan LIKE operator
     * serta escape wildcard characters untuk mencegah wildcard injection
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Product>  $query
     * @param  string  $term  Kata kunci pencarian
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    public function scopeSearch($query, string $term)
    {
        // Escape wildcard characters untuk mencegah wildcard injection attacks
        // dimana % dan _ di-escape menjadi \% dan \_ untuk keamanan
        $escapedTerm = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

        return $query->where(function ($q) use ($escapedTerm) {
            $q->where('name', 'like', "%{$escapedTerm}%")
                ->orWhere('description', 'like', "%{$escapedTerm}%");
        });
    }

    /**
     * Memeriksa apakah produk tersedia untuk dibeli
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Format harga dalam format Rupiah
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    /**
     * Mendapatkan status stok produk dengan kategorisasi
     * untuk menampilkan badge yang sesuai pada frontend
     *
     * @return array{status: string, label: string, stock: int}
     */
    public function getStockStatusAttribute(): array
    {
        if (! $this->is_active) {
            return [
                'status' => 'unavailable',
                'label' => 'Tidak Tersedia',
                'stock' => 0,
            ];
        }

        if ($this->stock <= 0) {
            return [
                'status' => 'out_of_stock',
                'label' => 'Habis',
                'stock' => 0,
            ];
        }

        if ($this->stock <= 5) {
            return [
                'status' => 'low_stock',
                'label' => 'Stok Terbatas',
                'stock' => $this->stock,
            ];
        }

        return [
            'status' => 'in_stock',
            'label' => 'Tersedia',
            'stock' => $this->stock,
        ];
    }
}
