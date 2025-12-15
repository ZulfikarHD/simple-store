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
     * Generate WhatsApp message dari customer ke owner
     * dengan tone yang sesuai, nama customer, dan link ke admin order detail
     */
    public function generateWhatsAppMessage(): string
    {
        $items = $this->items->map(function ($item) {
            return "- {$item->product_name} × {$item->quantity}";
        })->implode("\n");

        $adminUrl = url("/admin/orders/{$this->id}");

        return "Halo! Saya *{$this->customer_name}* ingin memesan.\n\n"
            ."*Invoice:* #{$this->order_number}\n\n"
            ."*Ringkasan Pesanan:*\n{$items}\n\n"
            .'*Total:* Rp '.number_format($this->total, 0, ',', '.')."\n\n"
            .($this->customer_address ? "*Alamat:* {$this->customer_address}\n\n" : '')
            .($this->notes ? "*Catatan:* {$this->notes}\n\n" : '')
            ."*Link Detail Pesanan:*\n{$adminUrl}\n\n"
            .'Mohon konfirmasi pesanan saya. Terima kasih!';
    }

    /**
     * Generate WhatsApp message dari owner ke customer
     * untuk konfirmasi atau update status pesanan
     */
    public function generateOwnerToCustomerMessage(string $type = 'confirmed'): string
    {
        $storeName = \App\Models\StoreSetting::get('store_name', 'Toko Kami');

        return match ($type) {
            'confirmed' => "Halo *{$this->customer_name}*! 👋\n\n"
                ."Pesanan Anda dengan nomor *#{$this->order_number}* telah *DIKONFIRMASI*. ✅\n\n"
                .'Total: *Rp '.number_format($this->total, 0, ',', '.')."*\n\n"
                ."Pesanan sedang kami proses. Terima kasih telah berbelanja di {$storeName}! 🙏",

            'preparing' => "Halo *{$this->customer_name}*! 👋\n\n"
                ."Pesanan *#{$this->order_number}* sedang *DIPROSES*. 🔄\n\n"
                .'Mohon tunggu sebentar ya. Terima kasih! 🙏',

            'ready' => "Halo *{$this->customer_name}*! 👋\n\n"
                ."Pesanan *#{$this->order_number}* sudah *SIAP*! 🎉\n\n"
                .'Silakan ambil pesanan Anda atau tunggu pengiriman. Terima kasih! 🙏',

            'delivered' => "Halo *{$this->customer_name}*! 👋\n\n"
                ."Pesanan *#{$this->order_number}* telah *DIKIRIM/SELESAI*. ✅\n\n"
                ."Terima kasih telah berbelanja di {$storeName}! "
                .'Semoga puas dengan pesanan Anda. 🙏',

            'cancelled' => "Halo *{$this->customer_name}*,\n\n"
                ."Mohon maaf, pesanan *#{$this->order_number}* telah *DIBATALKAN*. ❌\n\n"
                .($this->cancellation_reason ? "Alasan: {$this->cancellation_reason}\n\n" : '')
                .'Silakan hubungi kami jika ada pertanyaan. Terima kasih. 🙏',

            default => "Halo *{$this->customer_name}*! 👋\n\n"
                ."Update pesanan *#{$this->order_number}*.\n\n"
                ."Terima kasih telah berbelanja di {$storeName}! 🙏",
        };
    }

    /**
     * Generate WhatsApp URL untuk owner mengirim pesan ke customer
     * menggunakan StoreSettingService untuk format nomor berdasarkan phone_country_code
     */
    public function getWhatsAppToCustomerUrl(string $type = 'confirmed'): string
    {
        // Gunakan StoreSettingService untuk format nomor sesuai country code
        $phone = app(\App\Services\StoreSettingService::class)
            ->formatPhoneToInternational($this->customer_phone);

        $message = $this->generateOwnerToCustomerMessage($type);
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }

    /**
     * Cek apakah order sudah melewati batas waktu pending
     * dan harus di-auto-cancel
     */
    public function shouldAutoCancel(int $minutes): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return $this->created_at->addMinutes($minutes)->isPast();
    }
}
