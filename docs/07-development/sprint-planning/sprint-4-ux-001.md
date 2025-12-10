# Sprint 4: UX Enhancement - Order Tracking & WhatsApp Confirmation

**Sprint ID:** SPRINT-4-UX-001  
**Author:** Zulfikar Hidayatullah  
**Created:** 2025-12-10  
**Status:** ✅ Completed

---

## Feature Overview

Sprint ini berfokus pada peningkatan User Experience (UX) untuk fitur order tracking dan konfirmasi WhatsApp, yaitu:

1. **Order History & Detail untuk User** - Memungkinkan user melihat riwayat dan detail pesanan mereka
2. **WhatsApp Confirmation Enhancement** - Meningkatkan visibility tombol konfirmasi WhatsApp agar user tidak lupa
3. **Active Orders Section Redesign** - Horizontal scroll design untuk pesanan aktif di Home
4. **Cart Checkout Footer Fix** - Memperbaiki posisi sticky checkout footer pada mobile

---

## Business Case

### Problem Statement

1. **Order tidak terhubung ke user** - Order dibuat dengan `user_id: null` sehingga user tidak bisa melihat riwayat pesanan mereka
2. **User lupa konfirmasi WhatsApp** - Tombol WhatsApp di bagian bawah halaman success sering terlewat
3. **Tidak ada detail pesanan untuk user** - User tidak bisa melihat detail pesanan yang sudah dibuat
4. **Active orders mengambil terlalu banyak ruang vertikal** - Jika user punya banyak pesanan aktif, produk terdorong jauh ke bawah
5. **Checkout footer overlap dengan bottom nav** - Fixed footer tidak benar-benar fixed pada mobile

### Business Value

| Metric | Before | After | Impact |
|--------|--------|-------|--------|
| Order confirmation rate | ~60% (estimated) | ~90%+ | ↑ 30% |
| User engagement (order tracking) | N/A | Available | New feature |
| Mobile UX satisfaction | Low | High | Better retention |
| Support tickets (order status) | High | Low | ↓ Support load |

---

## User Flow

### 1. Order Confirmation Flow (Enhanced)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      ORDER SUCCESS PAGE                                  │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  ⚠️ IMPORTANT ALERT (Prominent at top)                            │  │
│  │  "Pesanan belum dikonfirmasi. Kirim WhatsApp untuk konfirmasi."   │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │                    ✓ Pesanan Berhasil!                            │  │
│  │                    Order #ORD-20251210-XXXXX                      │  │
│  │                                                                   │  │
│  │                    [Order Details...]                             │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ════════════════════════════════════════════════════════════════════   │
│  │ STICKY FOOTER (Mobile Only)                                      │   │
│  │ ┌──────────────────────────────────────────────────────────────┐ │   │
│  │ │  #ORD-20251210-XXXXX    [Konfirmasi via WhatsApp 📱]        │ │   │
│  │ └──────────────────────────────────────────────────────────────┘ │   │
│  ════════════════════════════════════════════════════════════════════   │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │                    BOTTOM NAV                                     │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

### 2. Order Detail Flow (New)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      ORDER DETAIL PAGE                                   │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  [← Kembali]  #ORD-20251210-XXXXX  [Status Badge]                 │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  ⚠️ WHATSAPP ALERT (Only for Pending Orders)                      │  │
│  │  "Menunggu konfirmasi. Kirim WhatsApp untuk konfirmasi."          │  │
│  │                              [Konfirmasi via WhatsApp 📱]         │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  STATUS TIMELINE                                                  │  │
│  │  ● Pending → ○ Confirmed → ○ Preparing → ○ Ready → ○ Delivered   │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  ORDER ITEMS                                                      │  │
│  │  - Nasi Goreng x2                              Rp 50.000          │  │
│  │  - Es Teh x1                                   Rp 5.000           │  │
│  │  ─────────────────────────────────────────────────────────────    │  │
│  │  Total                                         Rp 55.000          │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ════════════════════════════════════════════════════════════════════   │
│  │ STICKY FOOTER (Mobile, Pending Only)                             │   │
│  │ ┌──────────────────────────────────────────────────────────────┐ │   │
│  │ │  ⏳ Menunggu Konfirmasi    [Konfirmasi via WhatsApp 📱]      │ │   │
│  │ └──────────────────────────────────────────────────────────────┘ │   │
│  ════════════════════════════════════════════════════════════════════   │
└─────────────────────────────────────────────────────────────────────────┘
```

### 3. Active Orders Section (Redesigned)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         HOME PAGE                                        │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  📦 Pesanan Aktif (3)                                    [▼/▲]   │  │
│  │  ─────────────────────────────────────────────────────────────    │  │
│  │  ← HORIZONTAL SCROLL →                                            │  │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐                 │  │
│  │  │ Order 1 │ │ Order 2 │ │ Order 3 │ │ +2 lagi │                 │  │
│  │  │ Pending │ │ Ready   │ │ Prepar. │ │         │                 │  │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘                 │  │
│  │                                                                   │  │
│  │  [Lihat Semua Riwayat →]                                         │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  PRODUCT CATALOG (Now visible without excessive scrolling)        │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## API Endpoints

### 1. User Order Detail

**Endpoint:** `GET /account/orders/{order}`

**Authentication:** Required (user must own the order)

**Response:**

```json
{
  "component": "Account/OrderDetail",
  "props": {
    "order": {
      "id": 123,
      "order_number": "ORD-20251210-A7B9C",
      "customer_name": "John Doe",
      "customer_phone": "081234567890",
      "customer_address": "Jl. Sudirman No. 123",
      "notes": "Tidak pedas",
      "subtotal": 50000,
      "delivery_fee": 5000,
      "total": 55000,
      "status": "pending",
      "status_label": "Menunggu Konfirmasi",
      "items": [...],
      "items_count": 2,
      "cancellation_reason": null,
      "timestamps": {
        "created_at": "2025-12-10 14:30:00",
        "confirmed_at": null,
        "preparing_at": null,
        "ready_at": null,
        "delivered_at": null,
        "cancelled_at": null
      },
      "whatsapp_url": "https://wa.me/628xxx?text=..." // Only for pending orders
    }
  }
}
```

### 2. Shared Props Enhancement

**Location:** `HandleInertiaRequests.php`

**New shared prop:** `active_orders`

```php
'active_orders' => fn () => $request->user()
    ? [
        'count' => Order::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->count(),
        'orders' => Order::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->with(['items'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'items_count' => $order->items->count(),
                'items_preview' => $order->items->take(2)->pluck('product_name')->join(', '),
                'created_at' => $order->created_at->toISOString(),
                'created_at_human' => $order->created_at->diffForHumans(),
            ]),
    ]
    : ['count' => 0, 'orders' => []],
```

---

## Technical Implementation

### Files Modified

| File | Changes |
|------|---------|
| `app/Services/OrderService.php` | Added `user_id` to order creation, added `whatsapp_url` to `getOrderDetail()` for pending orders |
| `app/Http/Controllers/AccountController.php` | Added `orderShow()` method for user order detail |
| `routes/web.php` | Added route `GET /account/orders/{order}` |
| `resources/js/pages/OrderSuccess.vue` | Added warning alert, sticky WhatsApp footer |
| `resources/js/pages/Account/OrderDetail.vue` | New page for user order detail with WhatsApp CTA |
| `resources/js/pages/Account/Orders.vue` | Made order cards clickable to detail page |
| `resources/js/pages/Cart.vue` | Fixed checkout footer position (moved outside PullToRefresh) |
| `resources/js/components/mobile/ActiveOrdersSection.vue` | Redesigned with horizontal scroll |
| `resources/js/components/mobile/OrderTrackingCard.vue` | Link to order detail instead of orders list |
| `resources/css/app.css` | Added `.scrollbar-hide` utility |

### Key Code Changes

**1. Order Creation with User ID:**

```php
// app/Services/OrderService.php
'user_id' => auth()->id(), // Now saves authenticated user's ID
```

**2. WhatsApp URL for Pending Orders:**

```php
// app/Services/OrderService.php - getOrderDetail()
if ($order->status === 'pending') {
    $data['whatsapp_url'] = $this->generateWhatsAppUrl($order);
}
```

**3. Cart Checkout Footer Fix:**

```vue
<!-- Moved OUTSIDE PullToRefresh for true fixed positioning -->
<AnimatePresence>
    <Motion v-if="!isEmpty" ... class="fixed inset-x-0 bottom-20 z-40 ...">
        <!-- Checkout footer content -->
    </Motion>
</AnimatePresence>
```

**4. Horizontal Scroll for Active Orders:**

```vue
<div class="-mx-4 overflow-x-auto scrollbar-hide">
    <div class="flex gap-2.5 px-4">
        <OrderTrackingCard v-for="order in displayedOrders" ... class="w-64 shrink-0" />
    </div>
</div>
```

---

## Changelog

### Version 1.0.0 (2025-12-10)

**Added:**
- ✅ User order detail page (`/account/orders/{order}`)
- ✅ WhatsApp confirmation alert on OrderSuccess page (prominent at top)
- ✅ Sticky WhatsApp CTA footer on mobile for OrderSuccess
- ✅ WhatsApp confirmation alert on OrderDetail for pending orders
- ✅ Sticky WhatsApp CTA footer on mobile for pending order detail
- ✅ Horizontal scroll design for Active Orders section
- ✅ Order cards link directly to order detail
- ✅ `.scrollbar-hide` CSS utility for clean horizontal scroll

**Fixed:**
- ✅ Orders now save `user_id` for authenticated users (was `null`)
- ✅ Cart checkout footer now truly fixed (moved outside PullToRefresh)
- ✅ Bottom padding adjusted for checkout footer + bottom nav

**Changed:**
- ✅ Active Orders section uses horizontal scroll instead of vertical list
- ✅ Limited active orders preview to 5 items with "+X lagi" card
- ✅ OrderTrackingCard links to `/account/orders/{id}` instead of `/account/orders`

---

## Testing Checklist

- [x] User can view order history
- [x] User can view order detail
- [x] User sees WhatsApp alert on pending orders
- [x] WhatsApp button opens correct URL
- [x] Active orders scroll horizontally on mobile
- [x] Cart checkout footer stays fixed above bottom nav
- [x] Orders are linked to authenticated users
- [x] Unauthorized users cannot view other users' orders

---

## Screenshots

*Screenshots to be added after deployment*

---

**Document Version:** 1.0.0  
**Last Updated:** 2025-12-10  
**Author:** Zulfikar Hidayatullah

