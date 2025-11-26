<!-- f13489b6-a04f-45bb-842f-3e41f33565d8 1559ad87-abe1-4f3c-95cd-0b6cf7230192 -->
# Mobile UX Improvement Plan - Simple Store (Final)

## Overview

Transformasi aplikasi Simple Store dengan fokus pada:

1. **Admin**: Order-centric experience dengan quick actions dan alert system (HIGHEST PRIORITY)
2. **User**: Mobile app feel dengan bottom navigation (MEDIUM PRIORITY)

---

## BAGIAN 1: ADMIN SIDE (Prioritas Tinggi)

### 1.1 New Order Alert Banner

**Problem**: Badge di sidebar tidak terlihat pada mobile. Pesanan baru bisa terlewat.

**Solution**: Banner alert yang muncul di atas layar saat ada pesanan baru.

**Behavior**:

- Muncul otomatis saat ada pesanan baru (polling setiap 30 detik)
- Tetap tampil sampai di-dismiss atau pesanan diproses
- Menampilkan: Order number, customer name, total, waktu
- Tombol: [Lihat Detail] [Konfirmasi Langsung]

**Files**:

- Create: `resources/js/components/admin/NewOrderAlert.vue`
- Modify: `resources/js/composables/useOrderNotifications.ts` - tambah visual alert state
- Modify: `resources/js/layouts/AppLayout.vue` - integrate alert

### 1.2 Order Cards dengan Quick Actions

**Problem**: Untuk ubah status, admin harus buka detail → dropdown → dialog. Terlalu banyak tap.

**Solution**: Tombol aksi langsung di card pesanan.

**Design**:

```
┌────────────────────────────────────┐
│ ORD-240101-001        🟡 Pending   │
│ Budi Santoso • 3 item • Rp 85.000  │
│ ⏱️ 5 menit yang lalu               │
│ [📞 WhatsApp] [✓ Konfirmasi]       │
└────────────────────────────────────┘
```

**Tombol berubah sesuai status**:

- Pending → [Konfirmasi]
- Confirmed → [Proses]
- Preparing → [Siap Kirim]
- Ready → [Selesai]

**Files**:

- Create: `resources/js/components/admin/OrderCard.vue`
- Modify: `resources/js/pages/Admin/Orders/Index.vue` - gunakan cards pada mobile, table pada desktop

### 1.3 Urgency Indicators

**Problem**: Pesanan lama terlihat sama dengan yang baru.

**Solution**: Visual indicator berdasarkan waktu tunggu.

**Rules**:

- < 5 menit: Normal
- 5-15 menit: Warning (amber border)
- > 15 menit: Urgent (red border + pulse)

**Files**:

- Modify: `resources/js/components/admin/OrderCard.vue` - urgency styling
- Modify backend: Tambah `waiting_minutes` ke OrderResource

### 1.4 Admin Bottom Navigation

**Problem**: Sidebar membutuhkan tap hamburger menu setiap navigasi.

**Solution**: Bottom navigation untuk mobile.

**Tabs**: Pesanan (primary + badge), Produk, Kategori, Lainnya

**Files**:

- Create: `resources/js/components/admin/AdminBottomNav.vue`
- Create: `resources/js/layouts/AdminMobileLayout.vue`
- Modify: Admin pages untuk conditional layout

### 1.5 Order-Centric Default View

**Change**: Admin membuka app → langsung lihat Orders, bukan Dashboard.

**Files**:

- Modify: `routes/web.php` - redirect `/admin/dashboard` ke `/admin/orders` atau buat conditional

---

## BAGIAN 2: USER SIDE (Prioritas Medium)

### 2.1 User Bottom Navigation

**Goal**: Memberikan feel seperti mobile app native.

**Tabs**:

- Beranda (Home icon)
- Keranjang (Cart icon + badge)
- Akun (User icon)

**Note**: Tidak perlu tab Kategori karena hanya ada sedikit kategori (horizontal chips sudah cukup).

**Files**:

- Create: `resources/js/components/mobile/UserBottomNav.vue`
- Create: `resources/js/layouts/MobileStoreLayout.vue`
- Modify: `resources/js/pages/Home.vue`, `Cart.vue`, dll - integrate layout

### 2.2 Account Page (Nice to Have)

**Features**:

- Profile info untuk logged-in users
- Order history (list pesanan sebelumnya)
- Login/Register prompt untuk guest

**Files**:

- Create: `resources/js/pages/Account/Index.vue`
- Create: `resources/js/pages/Account/Orders.vue`
- Create: Controller & routes

### 2.3 Mobile Detection Utility

**Purpose**: Conditional rendering untuk mobile vs desktop layouts.

**Files**:

- Create: `resources/js/composables/useMobileDetect.ts`

---

## What We're NOT Doing (Decided Against)

| Feature | Reason |

|---------|--------|

| Full-screen Categories page | Hanya sedikit kategori, horizontal chips cukup |

| Checkout stepper wizard | Form sekarang sudah simple, stepper menambah tap |

| Cart swipe-to-delete | Overkill, tombol delete sudah ada |

| Floating search button | Search di header sudah cukup |

---

## Implementation Order

**Phase 1 - Admin Critical (Week 1)**

1. NewOrderAlert banner component
2. OrderCard dengan quick actions
3. Urgency indicators
4. Admin bottom navigation

**Phase 2 - Admin Polish (Week 1-2)**

5. Order-centric default view
6. Testing & refinement

**Phase 3 - User Side (Week 2)**

7. Mobile detection utility
8. User bottom navigation + layout
9. Account pages (if time permits)

---

## Technical Notes

- Polling interval: 30 detik untuk check new orders
- Bottom nav: `position: fixed` dengan `padding-bottom: env(safe-area-inset-bottom)` untuk iPhone
- Mobile breakpoint: < 768px (md breakpoint Tailwind)
- Preserve existing desktop experience - semua perubahan additive untuk mobile

### To-dos

- [x] Create NewOrderAlert banner component with polling
- [x] Create OrderCard component with quick action buttons
- [x] Add waiting time calculation and urgency styling
- [x] Create AdminBottomNav and AdminMobileLayout
- [x] Make Orders the default admin view on mobile
- [x] Create useMobileDetect composable
- [x] Create UserBottomNav and MobileStoreLayout
- [x] Create Account and Order History pages (nice-to-have)