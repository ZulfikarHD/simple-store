# Dashboard Overview

## Informasi Dokumen

| Atribut | Detail |
|---------|--------|
| **Nama Dokumen** | Panduan Dashboard Admin |
| **Developer** | Zulfikar Hidayatullah (+62 857-1583-8733) |
| **Versi** | 1.0.0 |

---

## Overview

Dashboard Admin merupakan halaman utama admin panel yang bertujuan untuk memberikan overview statistik dan monitoring toko secara real-time, yaitu: metrics penjualan, status pesanan, dan quick actions untuk operasional harian. Dashboard dirancang dengan iOS-style premium design dengan spring animations dan haptic feedback.

**URL**: `/admin/dashboard`

---

## Layout Dashboard

### Desktop Layout

```
┌──────────────────────────────────────────────────────────────┐
│  HEADER - Dashboard Title + Admin Badge                      │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────────────────────┐  ┌────────────┐ ┌────────┐  │
│  │     TOTAL PENJUALAN         │  │  PESANAN   │ │PENDING │  │
│  │     Rp X.XXX.XXX            │  │  HARI INI  │ │ ORDERS │  │
│  │     (Hero Card - 2 col)     │  │    XX      │ │   XX   │  │
│  └─────────────────────────────┘  └────────────┘ └────────┘  │
│                                                              │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────────────┐  │
│  │ PRODUK AKTIF │ │ NOTIFIKASI   │ │    QUICK ACTIONS     │  │
│  │     XX       │ │   Status     │ │  [Produk] [Kategori] │  │
│  └──────────────┘ └──────────────┘ └──────────────────────┘  │
│                                                              │
│  ┌────────────────────────────────┐ ┌──────────────────────┐ │
│  │      PESANAN TERBARU           │ │   STATUS PESANAN     │ │
│  │      (3 columns)               │ │    (2 columns)       │ │
│  │      - Order Card              │ │    - Pending: XX     │ │
│  │      - Order Card              │ │    - Confirmed: XX   │ │
│  │      - Order Card              │ │    - Preparing: XX   │ │
│  │      [Lihat Semua]             │ │    - Ready: XX       │ │
│  └────────────────────────────────┘ └──────────────────────┘ │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### Mobile Layout

```
┌─────────────────────┐
│  Dashboard          │
│  Selamat datang!    │
├─────────────────────┤
│ ┌─────────────────┐ │
│ │ TOTAL PENJUALAN │ │
│ │ Rp X.XXX.XXX    │ │
│ └─────────────────┘ │
│ ┌───────┐ ┌───────┐ │
│ │PESANAN│ │PENDING│ │
│ │  XX   │ │  XX   │ │
│ └───────┘ └───────┘ │
│ ┌─────────────────┐ │
│ │ PRODUK AKTIF    │ │
│ └─────────────────┘ │
│ ┌─────────────────┐ │
│ │ NOTIFIKASI      │ │
│ └─────────────────┘ │
│ ┌─────────────────┐ │
│ │ QUICK ACTIONS   │ │
│ └─────────────────┘ │
│ ┌─────────────────┐ │
│ │ PESANAN TERBARU │ │
│ │ - Order Card    │ │
│ │ - Order Card    │ │
│ └─────────────────┘ │
│ ┌─────────────────┐ │
│ │ STATUS BREAKDOWN│ │
│ └─────────────────┘ │
│                     │
│ [Bottom Nav Space]  │
└─────────────────────┘
```

---

## Statistics Cards

### 1. Total Penjualan (Hero Card)

**Deskripsi:** Card utama yang menampilkan total revenue keseluruhan.

| Attribute | Value |
|-----------|-------|
| Position | Row 1, spans 2 columns (lg) |
| Design | Premium gradient dengan decorative blur |
| Icon | 💰 Wallet (gold gradient) |
| Animation | Spring entrance dengan bounce |

**Data yang Ditampilkan:**
- Total sales dalam format Rupiah
- Trending indicator (arrow up)
- Label "Total revenue keseluruhan"

**Interaction:**
- Press feedback (scale 0.98)
- Haptic feedback on touch

### 2. Pesanan Hari Ini

**Deskripsi:** Jumlah pesanan yang masuk dalam satu hari.

| Attribute | Value |
|-----------|-------|
| Design | Blue themed card |
| Icon | 🛍️ Shopping Bag |
| Animation | Staggered entrance |

**Data yang Ditampilkan:**
- Angka jumlah pesanan hari ini
- Label "Pesanan masuk"

**Interaction:**
- Clickable → Navigate ke Orders page
- Haptic selection feedback

### 3. Pending Orders

**Deskripsi:** Pesanan yang menunggu konfirmasi (urgent indicator).

| Attribute | Value |
|-----------|-------|
| Design | Gold themed (jika ada pending) |
| Icon | ⏳ Clock |
| Animation | Pulse dot untuk alert |

**Data yang Ditampilkan:**
- Angka pending orders
- Animated pulse indicator (jika > 0)
- Label "Menunggu konfirmasi"

**Interaction:**
- Clickable → Navigate ke Orders page
- Urgent visual feedback jika pending > 0

### 4. Produk Aktif

**Deskripsi:** Jumlah produk yang statusnya aktif.

| Attribute | Value |
|-----------|-------|
| Design | Success themed (green) |
| Icon | 📦 Package |
| Animation | Slide in |

**Data yang Ditampilkan:**
- Angka produk aktif
- Chevron right indicator
- Label "Produk Aktif"

**Interaction:**
- Clickable → Navigate ke Products page
- Arrow shift on hover

---

## Notification Widget

### Status Notifikasi Browser

Widget yang menampilkan status browser notification:

| Status | Icon | Color | Button |
|--------|------|-------|--------|
| **Aktif** | 🔔 Bell | Green | - |
| **Diblokir** | 🔕 Bell Off | Red | - |
| **Tidak Aktif** | 🔔 Bell | Gray | "Aktifkan" |

### Mengaktifkan Notifikasi

1. Klik tombol "Aktifkan"
2. Browser meminta permission
3. Allow notification
4. Status berubah menjadi "Aktif"

### Kegunaan Notifikasi

- Alert pesanan baru masuk
- Reminder pending orders
- Real-time monitoring tanpa refresh manual

---

## Quick Actions

### Panel Quick Actions

Card dengan shortcut ke fitur yang sering digunakan:

| Action | Icon | Destination |
|--------|------|-------------|
| **Produk** | 📦 Package | `/admin/products` |
| **Kategori** | 📁 Folder Tree | `/admin/categories` |

### Design

- Pills/chips dengan icon
- Hover effect dengan color change
- Haptic feedback on tap

---

## Recent Orders Widget

### Deskripsi

Menampilkan 5-10 pesanan terbaru dengan informasi ringkas.

### Layout

```
┌────────────────────────────────────────────┐
│  📦 Pesanan Terbaru          [Lihat Semua] │
├────────────────────────────────────────────┤
│  ┌──────────────────────────────────────┐  │
│  │ #ORD-241211001     [Pending Badge]   │  │
│  │ John Doe                   Rp 75.000 │  │
│  │ 📦 3 item   📅 2 menit lalu      >  │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │ #ORD-241211002   [Confirmed Badge]   │  │
│  │ Jane Smith                 Rp 50.000 │  │
│  │ 📦 2 item   📅 5 menit lalu      >  │  │
│  └──────────────────────────────────────┘  │
│  ... (more orders)                         │
└────────────────────────────────────────────┘
```

### Informasi Per Order

| Field | Format |
|-------|--------|
| Order Number | `#ORD-XXXXXX` (primary color, clickable) |
| Status | Badge dengan warna sesuai status |
| Customer Name | Text nama lengkap |
| Total | Rupiah format (bold) |
| Items Count | Icon + jumlah item |
| Time | Human readable (X menit lalu) |
| Chevron | Indicator detail available |

### Interaction

- Klik order → Navigate ke detail pesanan
- Haptic selection feedback
- Hover effect pada desktop

### Empty State

Jika belum ada pesanan:
```
┌────────────────────────────────────────────┐
│             📦                              │
│        Belum Ada Pesanan                   │
│   Pesanan customer akan muncul di sini     │
│            secara real-time.               │
└────────────────────────────────────────────┘
```

---

## Status Breakdown Widget

### Deskripsi

Visual breakdown jumlah pesanan per status.

### Layout

```
┌────────────────────────────────────────────┐
│  📈 Status Pesanan                         │
├────────────────────────────────────────────┤
│  ┌──────────────────────────────────────┐  │
│  │ [Pending Badge]                   12 │  │
│  │                              pesanan │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │ [Confirmed Badge]                  8 │  │
│  │                              pesanan │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │ [Preparing Badge]                  5 │  │
│  │                              pesanan │  │
│  └──────────────────────────────────────┘  │
│  ... (more statuses)                       │
└────────────────────────────────────────────┘
```

### Data yang Ditampilkan

| Status | Badge Style |
|--------|-------------|
| Pending | Amber/Yellow |
| Confirmed | Blue |
| Preparing | Purple |
| Ready | Green |
| Delivered | Gray |
| Cancelled | Red |

### Empty State

Jika belum ada data:
```
⏳
Belum ada data status
```

---

## Animations & Interactions

### Spring Animations

Dashboard menggunakan spring-based animations:

| Element | Animation Type |
|---------|----------------|
| Page Header | Fade + slide up |
| Stats Cards | Scale + slide up (staggered) |
| Secondary Cards | Slide up (staggered) |
| Order Items | Slide from left (staggered) |
| Status Items | Slide from right (staggered) |

### Haptic Feedback

| Action | Haptic Type |
|--------|-------------|
| Card tap | Light |
| Quick action tap | Medium |
| Navigate to order | Selection |
| Card press | Light + scale feedback |

### Press Feedback

- Scale down to 0.98 on press
- Return to 1.0 on release
- Smooth spring transition

---

## Real-time Features

### Pull-to-Refresh

- Gesture: Tarik layar ke bawah
- Action: Reload dashboard data
- Indicator: Refresh spinner

### Polling for Pending Orders

- Interval: Setiap 30 detik
- Data: Pending orders count
- Action: Trigger notification jika ada pesanan baru

### Browser Notification

Ketika pesanan baru masuk:
```
🛍️ Pesanan Baru!
Ada 1 pesanan baru menunggu konfirmasi.
[View] [Dismiss]
```

---

## Data Requirements

### Props dari Backend

```typescript
interface DashboardStats {
  today_orders: number;         // Pesanan hari ini
  pending_orders: number;       // Pesanan pending
  total_sales: number;          // Total penjualan (Rupiah)
  active_products: number;      // Produk aktif
  recent_orders: OrderItem[];   // List pesanan terbaru
  order_status_breakdown: Record<string, number>; // Count per status
}

interface OrderItem {
  id: number;
  order_number: string;
  customer_name: string;
  customer_phone: string;
  total: number;
  status: string;
  items_count: number;
  created_at: string;
  created_at_human: string;     // "2 menit lalu"
}
```

---

## Best Practices

### Monitoring Harian

1. **Pagi Hari**
   - Cek pending orders dari semalam
   - Review status breakdown
   - Pastikan notifikasi browser aktif

2. **Sepanjang Hari**
   - Monitor real-time notifications
   - Respon cepat ke pending orders
   - Track total sales

3. **Akhir Hari**
   - Review today's orders
   - Check delivered vs cancelled ratio
   - Note any pending issues

### Response Guidelines

| Pending Duration | Action |
|-----------------|--------|
| < 5 menit | Normal, proses jika memungkinkan |
| 5-15 menit | Prioritaskan konfirmasi |
| > 15 menit | Urgent, segera konfirmasi atau hubungi |
| > 30 menit | Risk auto-cancel (jika enabled) |

---

## API Endpoint

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/admin/dashboard` | GET | Render dashboard dengan stats |

### Response Data

```php
return Inertia::render('Admin/Dashboard', [
    'stats' => [
        'today_orders' => $todayOrders,
        'pending_orders' => $pendingOrders,
        'total_sales' => $totalSales,
        'active_products' => $activeProducts,
        'recent_orders' => $recentOrders,
        'order_status_breakdown' => $statusBreakdown,
    ],
]);
```

---

## Troubleshooting

| Problem | Solusi |
|---------|--------|
| Data tidak update | Pull-to-refresh atau hard reload (Ctrl+Shift+R) |
| Notifikasi tidak muncul | Cek browser permission, enable di settings |
| Stats menunjukkan 0 | Pastikan ada data di database, cek koneksi |
| Animasi lag | Reduce animations di browser settings |
| Cards tidak clickable | Cek JavaScript console untuk errors |

