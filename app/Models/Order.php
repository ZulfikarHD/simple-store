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
     * Status dan timestamp fields tidak boleh mass-assignable
     * untuk mencegah manipulation via request parameters
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'access_ulid',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'notes',
        'subtotal',
        'delivery_fee',
        'total',
    ];

    /**
     * The attributes that aren't mass assignable.
     * Protected fields yang hanya bisa diubah via explicit setters
     *
     * @var list<string>
     */
    protected $guarded = [
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
     * Boot method untuk auto-generate order number dan ULID
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
            if (empty($order->access_ulid)) {
                $order->access_ulid = (string) Str::ulid();
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
     * menggunakan direct attribute assignment untuk bypass mass assignment protection
     */
    public function confirm(): bool
    {
        $this->status = 'confirmed';
        $this->confirmed_at = now();

        return $this->save();
    }

    /**
     * Update status order ke preparing
     * menggunakan direct attribute assignment untuk bypass mass assignment protection
     */
    public function startPreparing(): bool
    {
        $this->status = 'preparing';
        $this->preparing_at = now();

        return $this->save();
    }

    /**
     * Update status order ke ready
     * menggunakan direct attribute assignment untuk bypass mass assignment protection
     */
    public function markReady(): bool
    {
        $this->status = 'ready';
        $this->ready_at = now();

        return $this->save();
    }

    /**
     * Update status order ke delivered
     * menggunakan direct attribute assignment untuk bypass mass assignment protection
     */
    public function markDelivered(): bool
    {
        $this->status = 'delivered';
        $this->delivered_at = now();

        return $this->save();
    }

    /**
     * Update status order ke cancelled
     * menggunakan direct attribute assignment untuk bypass mass assignment protection
     */
    public function cancel(?string $reason = null): bool
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;

        return $this->save();
    }

    /**
     * Cek apakah akses ULID sudah expired berdasarkan status order
     * untuk mencegah akses ke order yang sudah selesai atau dibatalkan
     */
    public function isAccessExpired(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * Verifikasi nomor telepon customer untuk akses order via ULID
     * dengan normalisasi format untuk membandingkan hanya angka
     * dimana semua format dikonversi ke international format (62xxx)
     */
    public function verifyCustomerPhone(string $phone): bool
    {
        // Normalize input phone
        $normalizedInput = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xxx to 62xxx
        if (str_starts_with($normalizedInput, '0')) {
            $normalizedInput = '62'.substr($normalizedInput, 1);
        }

        // Normalize order phone
        $normalizedOrder = preg_replace('/[^0-9]/', '', $this->customer_phone);

        // Convert 08xxx to 62xxx
        if (str_starts_with($normalizedOrder, '0')) {
            $normalizedOrder = '62'.substr($normalizedOrder, 1);
        }

        return $normalizedInput === $normalizedOrder;
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
     * dengan tone yang sesuai, nama customer, dan ULID-based secure link
     * untuk menghindari exposure admin panel structure
     */
    public function generateWhatsAppMessage(): string
    {
        $items = $this->items->map(function ($item) {
            return "- {$item->product_name} × {$item->quantity}";
        })->implode("\n");

        // Gunakan ULID-based URL yang lebih secure
        $orderUrl = route('orders.view', ['ulid' => $this->access_ulid]);

        return "Halo! Saya *{$this->customer_name}* ingin memesan.\n\n"
            ."*Invoice:* #{$this->order_number}\n\n"
            ."*Ringkasan Pesanan:*\n{$items}\n\n"
            .'*Total:* Rp '.number_format($this->total, 0, ',', '.')."\n\n"
            .($this->customer_address ? "*Alamat:* {$this->customer_address}\n\n" : '')
            .($this->notes ? "*Catatan:* {$this->notes}\n\n" : '')
            ."*Link Detail Pesanan:*\n{$orderUrl}\n\n"
            .'Mohon konfirmasi pesanan saya. Terima kasih!';
    }

    /**
     * Generate WhatsApp message dari owner ke customer
     * untuk konfirmasi atau update status pesanan
     * Menggunakan template dari settings yang dapat di-customize oleh admin
     */
    public function generateOwnerToCustomerMessage(string $type = 'confirmed'): string
    {
        $settingService = app(\App\Services\StoreSettingService::class);

        // Ambil template dari settings
        $template = $settingService->getWhatsAppTemplate($type);

        // Jika template kosong, gunakan default fallback
        if (empty(trim($template))) {
            return $this->getDefaultOwnerToCustomerMessage($type);
        }

        // Parse template dengan variabel order
        return $settingService->parseTemplateVariables($template, $this);
    }

    /**
     * Get default message sebagai fallback jika template kosong
     * Mempertahankan format original untuk backward compatibility
     */
    private function getDefaultOwnerToCustomerMessage(string $type): string
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
