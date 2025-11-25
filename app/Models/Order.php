<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model Order untuk menyimpan data pesanan customer
 * dengan status tracking dan informasi pengiriman
 * serta relasi ke User dan OrderItem
 */
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'subtotal',
        'delivery_fee',
        'total',
        'status',
        'confirmed_at',
        'preparing_at',
        'ready_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Boot method untuk auto-generate order number
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number dengan format ORD-YYYYMMDD-XXXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));

        return "ORD-{$date}-{$random}";
    }

    /**
     * Mendapatkan user pemilik order ini
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan semua items dalam order ini
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope untuk filter order berdasarkan status
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Order>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Order>
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter order yang pending
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Order>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Order>
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Update status order ke confirmed
     */
    public function confirm(): bool
    {
        return $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Update status order ke preparing
     */
    public function startPreparing(): bool
    {
        return $this->update([
            'status' => 'preparing',
            'preparing_at' => now(),
        ]);
    }

    /**
     * Update status order ke ready
     */
    public function markReady(): bool
    {
        return $this->update([
            'status' => 'ready',
            'ready_at' => now(),
        ]);
    }

    /**
     * Update status order ke delivered
     */
    public function markDelivered(): bool
    {
        return $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Update status order ke cancelled
     */
    public function cancel(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Format total dalam format Rupiah
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format($this->total, 0, ',', '.');
    }

    /**
     * Generate WhatsApp message untuk order ini
     */
    public function generateWhatsAppMessage(): string
    {
        $items = $this->items->map(function ($item) {
            return "• {$item->product_name} x{$item->quantity} = Rp ".number_format($item->subtotal, 0, ',', '.');
        })->implode("\n");

        return "🛒 *PESANAN BARU*\n\n"
            ."📋 No. Pesanan: {$this->order_number}\n"
            ."👤 Nama: {$this->customer_name}\n"
            ."📱 Telepon: {$this->customer_phone}\n"
            ."📍 Alamat: {$this->customer_address}\n\n"
            ."*Detail Pesanan:*\n{$items}\n\n"
            .'💰 Subtotal: Rp '.number_format($this->subtotal, 0, ',', '.')."\n"
            .'🚚 Ongkir: Rp '.number_format($this->delivery_fee, 0, ',', '.')."\n"
            .'*Total: Rp '.number_format($this->total, 0, ',', '.')."*\n\n"
            .($this->notes ? "📝 Catatan: {$this->notes}\n\n" : '')
            .'Terima kasih telah memesan! 🙏';
    }
}
