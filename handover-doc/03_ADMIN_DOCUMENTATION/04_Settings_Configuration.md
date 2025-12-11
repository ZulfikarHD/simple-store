# Konfigurasi Pengaturan Toko

## Informasi Dokumen

| Atribut | Detail |
|---------|--------|
| **Nama Dokumen** | Panduan Konfigurasi Pengaturan |
| **Developer** | Zulfikar Hidayatullah (+62 857-1583-8733) |
| **Versi** | 1.0.0 |

---

## Overview

Halaman Pengaturan Toko merupakan pusat konfigurasi untuk mengelola semua aspek operasional toko, yaitu: informasi toko, WhatsApp bisnis, jam operasional, dan pengaturan delivery. Semua pengaturan tersimpan di tabel `store_settings` dengan format key-value.

**URL**: `/admin/settings`

---

## Struktur Data Settings

### Tabel `store_settings`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | Primary key |
| `key` | varchar | Nama setting (unique) |
| `value` | text | Nilai setting |
| `type` | varchar | Tipe data (string, json, boolean, integer) |
| `group` | varchar | Grup setting untuk organisasi |

### Default Settings

| Key | Type | Default | Deskripsi |
|-----|------|---------|-----------|
| `store_name` | string | "Simple Store" | Nama toko |
| `store_tagline` | string | "" | Tagline toko |
| `store_logo` | string | null | Path logo toko |
| `store_address` | string | "" | Alamat fisik toko |
| `store_phone` | string | "" | Nomor telepon toko |
| `whatsapp_number` | string | "" | Nomor WhatsApp bisnis |
| `operating_hours` | json | {...} | Jam operasional per hari |
| `delivery_areas` | json | [] | List area pengiriman |
| `delivery_fee` | integer | 0 | Biaya pengiriman (Rupiah) |
| `minimum_order` | integer | 0 | Minimum order (Rupiah) |
| `auto_cancel_enabled` | boolean | true | Auto-cancel pesanan pending |
| `auto_cancel_minutes` | integer | 30 | Durasi sebelum auto-cancel |

---

## Informasi Toko

### Logo Toko

**Field**: `store_logo`

Pengaturan logo yang ditampilkan di header storefront.

**Specifications:**
- Format: JPG, PNG, WebP
- Ukuran maksimal: 2MB
- Resolusi rekomendasi: 200x200px

**Cara Upload:**
1. Klik tombol "Upload Logo" atau area preview
2. Pilih file gambar dari device
3. Gambar langsung diupload (AJAX)
4. Preview update secara real-time
5. Klik "Simpan Pengaturan" untuk finalisasi

**Cara Menghapus:**
1. Klik tombol "Hapus Logo"
2. Logo akan dihapus dari storage
3. Toko akan tampil tanpa logo

### Nama Toko

**Field**: `store_name`

| Attribute | Value |
|-----------|-------|
| Type | Text |
| Required | ✅ |
| Max Length | 100 karakter |
| Placeholder | "Masukkan nama toko" |

Nama toko ditampilkan di:
- Header storefront
- WhatsApp messages
- Notifikasi email
- Title browser tab

### Tagline Toko

**Field**: `store_tagline`

| Attribute | Value |
|-----------|-------|
| Type | Text |
| Required | ❌ |
| Max Length | 200 karakter |
| Placeholder | "Contoh: Premium Quality Products" |

Tagline singkat yang ditampilkan di bawah nama toko pada header.

### Alamat Toko

**Field**: `store_address`

| Attribute | Value |
|-----------|-------|
| Type | Textarea |
| Required | ❌ |
| Max Length | 500 karakter |
| Placeholder | "Masukkan alamat lengkap toko" |

Alamat fisik toko yang ditampilkan di:
- Footer storefront
- WhatsApp messages (jika relevan)
- Halaman kontak

### Nomor Telepon

**Field**: `store_phone`

| Attribute | Value |
|-----------|-------|
| Type | Text |
| Required | ❌ |
| Format | Bebas (contoh: 021-1234567) |
| Placeholder | "021-1234567" |

Nomor telepon toko untuk kontak umum (bukan WhatsApp).

---

## WhatsApp Bisnis

### Nomor WhatsApp

**Field**: `whatsapp_number`

| Attribute | Value |
|-----------|-------|
| Type | Text |
| Required | ✅ |
| Format | Kode negara tanpa + (contoh: 6281234567890) |
| Placeholder | "6281234567890" |

**Format yang Valid:**
- ✅ `6281234567890` (Indonesia)
- ✅ `628123456789` (tanpa 0 di depan)
- ❌ `+6281234567890` (jangan pakai +)
- ❌ `081234567890` (harus dengan kode negara)

### Penggunaan WhatsApp

Nomor WhatsApp digunakan untuk:

1. **Konfirmasi Pesanan oleh Customer**
   - Tombol "Konfirmasi via WhatsApp" di halaman sukses order
   - Pesan pre-filled dengan detail pesanan

2. **Notifikasi Status oleh Admin**
   - Tombol WhatsApp di halaman detail pesanan
   - Template pesan untuk setiap status:
     - Konfirmasi pesanan
     - Pesanan sedang diproses
     - Pesanan siap diambil/dikirim
     - Pesanan selesai
     - Pembatalan pesanan

### Template WhatsApp Message

**Customer ke Toko (Konfirmasi Order):**
```
Halo, saya ingin mengkonfirmasi pesanan:

Order: #ORD-XXXXXX
Nama: [Nama Customer]
Telepon: [Nomor Telepon]

Items:
- [Produk 1] x [Qty] = Rp [Subtotal]
- [Produk 2] x [Qty] = Rp [Subtotal]

Total: Rp [Total]

Terima kasih!
```

**Admin ke Customer (Status Update):**
```
Halo [Nama Customer],

Pesanan Anda #ORD-XXXXXX telah [Status].

[Pesan sesuai status]

Terima kasih telah berbelanja di [Nama Toko]!
```

---

## Jam Operasional

### Struktur Data

**Field**: `operating_hours`
**Type**: JSON

```json
{
  "monday": { "open": "08:00", "close": "21:00", "is_open": true },
  "tuesday": { "open": "08:00", "close": "21:00", "is_open": true },
  "wednesday": { "open": "08:00", "close": "21:00", "is_open": true },
  "thursday": { "open": "08:00", "close": "21:00", "is_open": true },
  "friday": { "open": "08:00", "close": "21:00", "is_open": true },
  "saturday": { "open": "09:00", "close": "22:00", "is_open": true },
  "sunday": { "open": "09:00", "close": "20:00", "is_open": true }
}
```

### Konfigurasi Per Hari

Untuk setiap hari, admin dapat mengatur:

| Field | Deskripsi |
|-------|-----------|
| `is_open` | Toggle buka/tutup |
| `open` | Jam buka (format 24 jam: HH:MM) |
| `close` | Jam tutup (format 24 jam: HH:MM) |

### Cara Mengatur

1. **Buka/Tutup Hari Tertentu**
   - Centang/uncheck checkbox di samping nama hari
   - Jika unchecked, tampil "Toko tutup hari ini"

2. **Atur Jam Operasi**
   - Pilih jam buka dari time picker
   - Pilih jam tutup dari time picker
   - Format 24 jam (contoh: 08:00, 21:00)

### Best Practices

- Konsisten dengan jadwal operasional fisik
- Update jika ada perubahan jam (libur nasional, dll)
- Pertimbangkan jam sibuk untuk customer service

---

## Pengaturan Delivery

### Biaya Pengiriman

**Field**: `delivery_fee`

| Attribute | Value |
|-----------|-------|
| Type | Number |
| Required | ✅ |
| Min Value | 0 |
| Unit | Rupiah (Rp) |
| Default | 0 |

Biaya pengiriman yang akan ditambahkan ke total pesanan.

**Contoh:**
- Gratis ongkir: 0
- Flat rate: 10000

### Minimum Order

**Field**: `minimum_order`

| Attribute | Value |
|-----------|-------|
| Type | Number |
| Required | ✅ |
| Min Value | 0 |
| Unit | Rupiah (Rp) |
| Default | 0 |

Nilai minimum pesanan agar customer dapat checkout.

**Contoh:**
- Tanpa minimum: 0
- Minimum Rp 50.000: 50000

### Area Pengiriman

**Field**: `delivery_areas`
**Type**: JSON Array

List area/wilayah yang dilayani untuk pengiriman.

**Cara Menambah Area:**
1. Ketik nama area di input field
2. Klik tombol "+" atau tekan Enter
3. Area ditambahkan sebagai tag/badge

**Cara Menghapus Area:**
1. Klik tombol "×" pada badge area
2. Area langsung terhapus dari list

**Contoh:**
```json
["Jakarta Selatan", "Jakarta Pusat", "Depok", "Tangerang Selatan"]
```

---

## Auto-Cancel Pesanan

### Fitur Auto-Cancel

**Field**: `auto_cancel_enabled` & `auto_cancel_minutes`

Fitur ini memungkinkan sistem untuk otomatis membatalkan pesanan dengan status "Pending" yang tidak dikonfirmasi dalam waktu tertentu.

### Mengaktifkan Auto-Cancel

| Setting | Deskripsi |
|---------|-----------|
| Toggle On/Off | Enable/disable fitur |
| Durasi (menit) | Waktu tunggu sebelum auto-cancel |

**Default:**
- Enabled: ✅ (aktif)
- Durasi: 30 menit

### Cara Kerja

1. Customer membuat pesanan → Status: **Pending**
2. Timer mulai berjalan
3. Jika dalam [X] menit tidak dikonfirmasi:
   - Status berubah menjadi **Cancelled**
   - Alasan: "Pesanan otomatis dibatalkan karena tidak dikonfirmasi"
4. Jika dikonfirmasi sebelum timeout:
   - Timer dihentikan
   - Status berubah sesuai konfirmasi

### Rekomendasi Durasi

| Situasi | Durasi |
|---------|--------|
| Respon cepat | 15-30 menit |
| Normal | 30-60 menit |
| Jam operasional terbatas | 60-120 menit |

---

## Regional Settings

### Default Configuration

Simple Store dikonfigurasi dengan regional settings Indonesia:

| Setting | Value |
|---------|-------|
| **Timezone** | Asia/Jakarta (WIB) |
| **Currency** | Rupiah (Rp) |
| **Language** | Indonesian |
| **Date Format** | DD MMM YYYY |
| **Time Format** | 24-hour (HH:MM) |

### Konfigurasi Timezone (Backend)

Timezone dikonfigurasi di `config/app.php`:

```php
'timezone' => 'Asia/Jakarta',
```

### Format Currency

Currency format di frontend menggunakan Intl.NumberFormat:

```javascript
new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR'
})
```

---

## Menyimpan Pengaturan

### Proses Simpan

1. Klik tombol "Simpan Pengaturan" di sidebar
2. Sistem memvalidasi semua input
3. Data disimpan ke tabel `store_settings`
4. Flash message sukses ditampilkan
5. Halaman tetap di settings (preserveScroll)

### Validasi

| Field | Validasi |
|-------|----------|
| store_name | Required, max 100 chars |
| whatsapp_number | Required, numeric string |
| delivery_fee | Required, numeric, min 0 |
| minimum_order | Required, numeric, min 0 |
| auto_cancel_minutes | Required if enabled, 5-1440 |

### Error Handling

Jika validasi gagal:
- Error message ditampilkan di bawah field
- Border field berubah merah
- Focus otomatis ke field error pertama

---

## Troubleshooting

| Problem | Solusi |
|---------|--------|
| Settings tidak tersimpan | Cek validasi error, pastikan semua required field terisi |
| Logo tidak muncul | Jalankan `php artisan storage:link` |
| WhatsApp tidak berfungsi | Cek format nomor (tanpa +, dengan kode negara) |
| Jam operasional salah | Cek format time (24 jam) dan timezone |
| Delivery area hilang | Pastikan klik Simpan setelah menambah area |

---

## API Endpoint

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/admin/settings` | GET | Tampilkan halaman settings |
| `/admin/settings` | PATCH | Update settings |
| `/admin/settings/upload-logo` | POST | Upload logo toko |

### Request Body (PATCH)

```json
{
  "store_name": "Nama Toko",
  "store_tagline": "Tagline",
  "store_address": "Alamat",
  "store_phone": "021-1234567",
  "whatsapp_number": "6281234567890",
  "operating_hours": {...},
  "delivery_areas": ["Area 1", "Area 2"],
  "delivery_fee": 10000,
  "minimum_order": 50000,
  "auto_cancel_enabled": true,
  "auto_cancel_minutes": 30
}
```

### Response (Success)

```json
{
  "message": "Pengaturan berhasil disimpan."
}
```
