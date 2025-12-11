# Manajemen Pesanan

## Informasi Dokumen

| Atribut | Detail |
|---------|--------|
| **Nama Dokumen** | Panduan Manajemen Pesanan |
| **Developer** | Zulfikar Hidayatullah (+62 857-1583-8733) |
| **Versi** | 1.0.0 |

---

## Overview

Manajemen Pesanan pada Simple Store merupakan modul yang bertujuan untuk mengelola seluruh siklus hidup pesanan, yaitu: dari pembuatan order hingga pengiriman, dengan fitur real-time monitoring, status tracking, dan integrasi WhatsApp untuk komunikasi dengan customer.

---

## URL Akses

| Halaman | URL |
|---------|-----|
| Daftar Pesanan | `/admin/orders` |
| Detail Pesanan | `/admin/orders/{id}` |

---

## Struktur Data Pesanan

### Tabel `orders`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | Primary key |
| `order_number` | varchar | Nomor pesanan unik (ORD-XXXXXX) |
| `user_id` | integer | ID user (nullable untuk guest) |
| `customer_name` | varchar | Nama customer |
| `customer_phone` | varchar | Nomor telepon/WhatsApp |
| `customer_address` | text | Alamat pengiriman |
| `notes` | text | Catatan dari customer |
| `subtotal` | numeric | Total sebelum ongkir |
| `delivery_fee` | numeric | Biaya pengiriman |
| `total` | numeric | Grand total |
| `status` | varchar | Status pesanan |
| `confirmed_at` | datetime | Waktu konfirmasi |
| `preparing_at` | datetime | Waktu mulai diproses |
| `ready_at` | datetime | Waktu siap |
| `delivered_at` | datetime | Waktu selesai |
| `cancelled_at` | datetime | Waktu pembatalan |
| `cancellation_reason` | text | Alasan pembatalan |
| `created_at` | datetime | Waktu order dibuat |
| `updated_at` | datetime | Waktu update terakhir |

### Tabel `order_items`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | Primary key |
| `order_id` | integer | Foreign key ke orders |
| `product_id` | integer | Foreign key ke products |
| `product_name` | varchar | Nama produk (snapshot) |
| `product_price` | numeric | Harga satuan (snapshot) |
| `quantity` | integer | Jumlah item |
| `subtotal` | numeric | Subtotal item |
| `notes` | text | Catatan per item |

---

## Status Pesanan

### Status Lifecycle

```
┌─────────┐     ┌───────────┐     ┌───────────┐     ┌─────────┐     ┌───────────┐
│ PENDING │ ──▶ │ CONFIRMED │ ──▶ │ PREPARING │ ──▶ │  READY  │ ──▶ │ DELIVERED │
└─────────┘     └───────────┘     └───────────┘     └─────────┘     └───────────┘
      │                                                    
      │         ┌───────────┐
      └────────▶│ CANCELLED │
                └───────────┘
```

### Detail Status

| Status | Badge Color | Icon | Deskripsi |
|--------|-------------|------|-----------|
| **Pending** | 🟡 Amber | ⏳ Clock | Pesanan baru, menunggu konfirmasi |
| **Confirmed** | 🔵 Blue | ✅ CheckCircle | Pesanan dikonfirmasi, akan diproses |
| **Preparing** | 🟣 Purple | 👨‍🍳 Chef Hat | Pesanan sedang disiapkan |
| **Ready** | 🟢 Green | 🚚 Truck | Pesanan siap diambil/dikirim |
| **Delivered** | ⚫ Gray | ✔️ Circle Check | Pesanan selesai |
| **Cancelled** | 🔴 Red | ❌ X Circle | Pesanan dibatalkan |

### Timestamp Tracking

Setiap perubahan status dicatat dengan timestamp:

| Status | Timestamp Field |
|--------|-----------------|
| Pending | `created_at` |
| Confirmed | `confirmed_at` |
| Preparing | `preparing_at` |
| Ready | `ready_at` |
| Delivered | `delivered_at` |
| Cancelled | `cancelled_at` |

---

## Daftar Pesanan

### Tampilan Desktop

Halaman daftar pesanan menampilkan tabel dengan kolom:
- **No. Pesanan** - Order number yang clickable
- **Customer** - Nama dan nomor telepon (dengan link WhatsApp)
- **Total** - Grand total dalam Rupiah
- **Items** - Jumlah item dalam pesanan
- **Status** - Badge status dengan warna
- **Tanggal** - Waktu pembuatan pesanan
- **Aksi** - Tombol Detail

### Tampilan Mobile

Untuk pengalaman mobile yang optimal:
- **Status Tab Pills** - Quick filter berdasarkan status
- **Order Cards** - Kartu informasi pesanan yang compact
- **Pull-to-Refresh** - Refresh data dengan gesture
- **Swipe Actions** - Aksi cepat pada kartu

### Filter dan Pencarian

**Search Bar:**
- Cari berdasarkan order number
- Cari berdasarkan nama customer
- Cari berdasarkan nomor telepon

**Filter Status:**
- Dropdown atau tab pills untuk filter status
- Badge counter menunjukkan jumlah per status

**Filter Tanggal:**
- Date picker untuk start date
- Date picker untuk end date
- Filter rentang waktu pesanan

**Sorting:**
- Sort by Order Number (A-Z / Z-A)
- Sort by Customer Name
- Sort by Total (High-Low / Low-High)
- Sort by Items Count
- Sort by Status
- Sort by Date (Newest / Oldest)

---

## Detail Pesanan

### URL

`/admin/orders/{id}`

### Informasi yang Ditampilkan

#### 1. Header Pesanan
- Order number
- Status badge
- Waktu pembuatan

#### 2. Informasi Customer
- Nama lengkap
- Nomor telepon (dengan tombol WhatsApp)
- Alamat pengiriman
- Catatan dari customer

#### 3. Item Pesanan
Tabel dengan kolom:
- Nama produk
- Harga satuan
- Quantity
- Subtotal

#### 4. Ringkasan Pembayaran
- Subtotal (sebelum ongkir)
- Biaya pengiriman
- **Grand Total**

#### 5. Status Timeline
Visual timeline menunjukkan:
- Status saat ini (highlighted)
- Status yang sudah dilalui (dengan timestamp)
- Status yang belum dilalui (disabled)

#### 6. WhatsApp Actions
Tombol untuk mengirim notifikasi ke customer via WhatsApp:
- Konfirmasi pesanan
- Pesanan sedang diproses
- Pesanan siap
- Pesanan selesai
- Pembatalan pesanan

---

## Mengubah Status Pesanan

### Flow Perubahan Status

1. **Pending → Confirmed**
   - Admin mengkonfirmasi pesanan valid
   - Timestamp `confirmed_at` dicatat
   - Customer dapat dikirim notifikasi WhatsApp

2. **Confirmed → Preparing**
   - Pesanan mulai diproses
   - Timestamp `preparing_at` dicatat

3. **Preparing → Ready**
   - Pesanan siap diambil/dikirim
   - Timestamp `ready_at` dicatat
   - Notifikasi ke customer disarankan

4. **Ready → Delivered**
   - Pesanan sudah diterima customer
   - Timestamp `delivered_at` dicatat
   - Status final (tidak bisa diubah lagi)

### Cara Mengubah Status

**Via Detail Page:**
1. Buka halaman detail pesanan
2. Pilih status baru dari dropdown/buttons
3. Konfirmasi perubahan
4. Status terupdate dengan timestamp

**Via Quick Actions (Mobile):**
1. Di daftar pesanan, tap order card
2. Gunakan quick action buttons
3. Status langsung terupdate

### Pembatalan Pesanan

**Kapan bisa dibatalkan:**
- Status Pending (belum dikonfirmasi)
- Status Confirmed (sebelum diproses)

**Cara membatalkan:**
1. Buka detail pesanan
2. Klik tombol "Batalkan Pesanan"
3. Masukkan alasan pembatalan (wajib)
4. Konfirmasi pembatalan
5. Status berubah menjadi Cancelled
6. Kirim notifikasi ke customer via WhatsApp

**Alasan pembatalan umum:**
- Stok produk habis
- Customer tidak merespon konfirmasi
- Alamat tidak valid
- Pembatalan atas permintaan customer
- Lainnya (custom)

---

## Integrasi WhatsApp

### WhatsApp ke Customer

Dari halaman detail pesanan, admin dapat mengirim pesan WhatsApp dengan template:

| Template | Penggunaan |
|----------|------------|
| **Konfirmasi** | Memberitahu pesanan sudah dikonfirmasi |
| **Preparing** | Memberitahu pesanan sedang diproses |
| **Ready** | Memberitahu pesanan siap diambil/dikirim |
| **Delivered** | Konfirmasi pesanan sudah diterima |
| **Cancelled** | Memberitahu pembatalan beserta alasan |

### Format Pesan

**Konfirmasi Pesanan:**
```
Halo [Nama Customer],

Pesanan Anda #ORD-XXXXXX telah dikonfirmasi! ✅

Kami akan segera memprosesnya.

Terima kasih telah berbelanja di [Nama Toko]!
```

**Pesanan Siap:**
```
Halo [Nama Customer],

Pesanan Anda #ORD-XXXXXX sudah siap! 📦

Silakan segera ambil pesanan Anda.

Terima kasih telah berbelanja di [Nama Toko]!
```

**Pembatalan:**
```
Halo [Nama Customer],

Mohon maaf, pesanan Anda #ORD-XXXXXX terpaksa dibatalkan.

Alasan: [Alasan Pembatalan]

Silakan hubungi kami untuk informasi lebih lanjut.

Terima kasih atas pengertiannya.
```

---

## Browser Notifications

### Fitur Notifikasi

Admin dapat mengaktifkan browser notifications untuk:
- Alert pesanan baru masuk (Pending)
- Notifikasi jumlah pending orders

### Cara Mengaktifkan

1. Di Dashboard, lihat widget Notifikasi
2. Klik tombol "Aktifkan"
3. Browser akan meminta izin
4. Klik "Allow" / "Izinkan"
5. Notifikasi akan muncul saat ada pesanan baru

### Polling System

Sistem melakukan polling ke server untuk mengecek:
- Jumlah pending orders
- Pesanan baru yang masuk
- Interval: setiap 30 detik

---

## Auto-Cancel System

### Cara Kerja

1. Pesanan dibuat dengan status Pending
2. Timer auto-cancel mulai berjalan
3. Jika dalam X menit tidak dikonfirmasi:
   - Status otomatis berubah ke Cancelled
   - Alasan: "Pesanan otomatis dibatalkan karena tidak dikonfirmasi dalam [X] menit"
4. Jika dikonfirmasi sebelum timeout:
   - Timer dihentikan
   - Status berubah ke Confirmed

### Konfigurasi

Lihat `04_Settings_Configuration.md` untuk pengaturan:
- Enable/disable auto-cancel
- Durasi timeout (menit)

---

## Monitoring & Analytics

### Dashboard Metrics

| Metric | Deskripsi |
|--------|-----------|
| **Total Sales** | Total revenue keseluruhan |
| **Today Orders** | Jumlah pesanan hari ini |
| **Pending Orders** | Pesanan menunggu konfirmasi |
| **Status Breakdown** | Distribusi pesanan per status |

### Recent Orders Widget

Dashboard menampilkan 5-10 pesanan terbaru dengan:
- Order number
- Customer name
- Total
- Status
- Waktu

### Waiting Time Indicator

Untuk pesanan Pending, sistem menampilkan:
- Berapa lama pesanan menunggu
- Warning indicator untuk pesanan > 15 menit

---

## Best Practices

### Respon Time

1. **Target Respon Pending Orders**
   - Ideal: < 15 menit
   - Maksimal: < 30 menit (sebelum auto-cancel)

2. **Update Status Real-time**
   - Update status sesuai progress aktual
   - Jangan skip status (Pending → Ready)

3. **Komunikasi dengan Customer**
   - Kirim notifikasi WhatsApp di setiap perubahan status
   - Respon cepat jika customer menghubungi

### Penanganan Pesanan

1. **Konfirmasi Pesanan**
   - Verifikasi stok tersedia
   - Cek alamat pengiriman valid (jika delivery)
   - Hubungi customer jika ada yang kurang jelas

2. **Proses Pesanan**
   - Update status Preparing saat mulai proses
   - Persiapkan sesuai urutan masuk (FIFO)

3. **Pesanan Siap**
   - Update status Ready
   - Kirim notifikasi ke customer
   - Siapkan untuk pickup/delivery

4. **Penyelesaian**
   - Update status Delivered setelah customer terima
   - Minta feedback jika perlu

---

## Troubleshooting

| Problem | Solusi |
|---------|--------|
| Pesanan tidak muncul | Refresh halaman, cek filter aktif |
| Status tidak bisa diubah | Cek sequence status yang valid |
| WhatsApp tidak berfungsi | Cek nomor customer valid, cek setting WhatsApp |
| Notifikasi tidak muncul | Aktifkan browser notification, cek permission |
| Auto-cancel tidak berjalan | Cek setting auto-cancel di Pengaturan |

---

## API Endpoints

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/admin/orders` | GET | Daftar pesanan (paginated) |
| `/admin/orders/{id}` | GET | Detail pesanan |
| `/admin/orders/{id}/status` | PATCH | Update status pesanan |
| `/admin/api/orders/pending` | GET | Cek pending orders count |
| `/admin/api/orders/{id}/quick-status` | PATCH | Quick status update |

### Request Body (Update Status)

```json
{
  "status": "confirmed",
  "cancellation_reason": null
}
```

### Request Body (Cancel Order)

```json
{
  "status": "cancelled",
  "cancellation_reason": "Stok produk habis"
}
```

### Response

```json
{
  "message": "Status pesanan berhasil diperbarui."
}
```

