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
| `store_favicon` | string | null | Path favicon toko |
| `store_address` | string | "" | Alamat fisik toko |
| `store_phone` | string | "" | Nomor telepon toko |
| `whatsapp_number` | string | "" | Nomor WhatsApp bisnis |
| `phone_country_code` | string | "ID" | Kode negara untuk format nomor telepon |
| `operating_hours` | json | {...} | Jam operasional per hari |
| `delivery_areas` | json | [] | List area pengiriman |
| `delivery_fee` | integer | 0 | Biaya pengiriman (Rupiah) |
| `minimum_order` | integer | 0 | Minimum order (Rupiah) |
| `auto_cancel_enabled` | boolean | true | Auto-cancel pesanan pending |
| `auto_cancel_minutes` | integer | 30 | Durasi sebelum auto-cancel |
| `whatsapp_template_confirmed` | text | (default) | Template pesan konfirmasi |
| `whatsapp_template_preparing` | text | (default) | Template pesan diproses |
| `whatsapp_template_ready` | text | (default) | Template pesan siap |
| `whatsapp_template_delivered` | text | (default) | Template pesan dikirim |
| `whatsapp_template_cancelled` | text | (default) | Template pesan dibatalkan |
| `timeline_icons` | json | {...} | Mapping icon timeline per status |

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

### Favicon Toko

**Field**: `store_favicon`

Pengaturan favicon (icon browser tab) yang ditampilkan di browser.

**Specifications:**
- Format: PNG, ICO, SVG, JPG, WebP
- Ukuran maksimal: 1MB
- Resolusi rekomendasi: 32x32px atau 64x64px

**Cara Upload:**
1. Klik tombol "Upload Favicon" di section Informasi Toko
2. Pilih file gambar dari device
3. Gambar langsung diupload (AJAX)
4. Preview update secara real-time
5. Klik "Simpan Pengaturan" untuk finalisasi

**Cara Menghapus:**
1. Klik tombol "Hapus Favicon"
2. Favicon akan dihapus dari storage
3. Browser akan menggunakan favicon default

**Catatan:**
- Favicon akan muncul di tab browser untuk semua halaman
- Jika tidak diset, sistem menggunakan `/favicon.ico` default
- Perubahan favicon mungkin memerlukan hard refresh (Ctrl+F5)

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

### Negara/Region

**Field**: `phone_country_code`

| Attribute | Value |
|-----------|-------|
| Type | Dropdown/Select |
| Required | ✅ |
| Default | ID (Indonesia) |

Pengaturan negara/region yang menentukan format kode telepon internasional untuk integrasi WhatsApp, yaitu: sistem akan otomatis mengkonversi nomor lokal ke format internasional berdasarkan negara yang dipilih.

**Negara yang Didukung:**

| Kode | Negara | Calling Code |
|------|--------|--------------|
| ID | Indonesia | +62 |
| MY | Malaysia | +60 |
| SG | Singapore | +65 |
| PH | Philippines | +63 |
| TH | Thailand | +66 |
| VN | Vietnam | +84 |
| US | United States | +1 |
| AU | Australia | +61 |

**Konversi Otomatis:**
- Nomor `081234567890` dengan region `ID` → `6281234567890`
- Nomor `0123456789` dengan region `MY` → `60123456789`
- Nomor `91234567` dengan region `SG` → `6591234567`

### Nomor WhatsApp

**Field**: `whatsapp_number`

| Attribute | Value |
|-----------|-------|
| Type | Text |
| Required | ✅ |
| Format | Bisa dengan atau tanpa kode negara |
| Placeholder | "081234567890" atau "6281234567890" |

**Format yang Valid (dengan multi-region support):**
- ✅ `081234567890` (lokal Indonesia, akan di-convert ke 62)
- ✅ `6281234567890` (sudah dengan kode negara)
- ✅ `0123456789` (lokal Malaysia, akan di-convert ke 60)
- ❌ `+6281234567890` (jangan pakai tanda +)

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

## Template Pesan WhatsApp (Customizable)

### Overview

Admin dapat mengkustomisasi template pesan WhatsApp yang dikirim ke customer saat status pesanan berubah. Fitur ini memungkinkan personalisasi pesan sesuai brand dan kebutuhan bisnis.

### Cara Mengakses

1. Buka halaman **Pengaturan Toko** (`/admin/settings`)
2. Scroll ke section **"Template Pesan WhatsApp"**
3. Pilih tab status yang ingin diedit

### Status Template yang Tersedia

| Status | Tab | Deskripsi |
|--------|-----|-----------|
| **Confirmed** | 🔵 Dikonfirmasi | Pesan saat pesanan dikonfirmasi |
| **Preparing** | 🟡 Diproses | Pesan saat pesanan mulai diproses |
| **Ready** | 🟢 Siap | Pesan saat pesanan siap diambil/dikirim |
| **Delivered** | 🟢 Dikirim | Pesan saat pesanan selesai |
| **Cancelled** | 🔴 Dibatalkan | Pesan saat pesanan dibatalkan |

### Variabel yang Tersedia

Gunakan variabel berikut dalam template untuk data dinamis:

| Variabel | Deskripsi | Contoh Output |
|----------|-----------|---------------|
| `{customer_name}` | Nama customer | John Doe |
| `{order_number}` | Nomor pesanan | ORD-20251215-ABC12 |
| `{total}` | Total pesanan (formatted) | Rp 150.000 |
| `{store_name}` | Nama toko | Simple Store |
| `{cancellation_reason}` | Alasan pembatalan | Alasan: Stok habis |

### Cara Mengedit Template

1. Klik tab status yang ingin diedit
2. Edit teks di textarea
3. Klik tombol variabel untuk menyisipkan variabel
4. Lihat preview di bawah textarea
5. Klik **"Simpan Pengaturan"**

### Fitur Template Editor

- **Insert Variable Buttons**: Klik untuk menyisipkan variabel ke posisi cursor
- **Live Preview**: Lihat hasil template dengan sample data
- **Reset Default**: Kembalikan ke template default
- **Max 2000 karakter** per template

### Contoh Template Default

**Konfirmasi Pesanan:**
```
Halo *{customer_name}*! 👋

Pesanan Anda dengan nomor *#{order_number}* telah *DIKONFIRMASI*. ✅

Total: *{total}*

Pesanan sedang kami proses. Terima kasih telah berbelanja di {store_name}! 🙏
```

**Pesanan Dibatalkan:**
```
Halo *{customer_name}*,

Mohon maaf, pesanan *#{order_number}* telah *DIBATALKAN*. ❌

{cancellation_reason}

Silakan hubungi kami jika ada pertanyaan. Terima kasih. 🙏
```

### Best Practices

1. **Gunakan emoji** untuk membuat pesan lebih friendly
2. **Bold text** dengan asterisk (*) untuk highlight penting
3. **Sertakan variabel** `{store_name}` untuk branding
4. **Keep it short** - pesan singkat lebih efektif
5. **Test preview** sebelum menyimpan

---

## Icon Timeline Status (Customizable)

### Overview

Admin dapat mengkustomisasi icon yang ditampilkan di timeline status pesanan. Fitur ini memungkinkan personalisasi visual sesuai brand.

### Cara Mengakses

1. Buka halaman **Pengaturan Toko** (`/admin/settings`)
2. Scroll ke section **"Icon Timeline Status"**
3. Klik icon yang ingin diubah

### Status Icon yang Dapat Diubah

| Status | Default Icon | Deskripsi |
|--------|--------------|-----------|
| **Created** | Clock | Pesanan dibuat |
| **Pending** | Clock | Menunggu konfirmasi |
| **Confirmed** | CheckCircle2 | Dikonfirmasi |
| **Preparing** | ChefHat | Sedang diproses |
| **Ready** | Package | Siap diambil/dikirim |
| **Delivered** | Truck | Selesai dikirim |
| **Cancelled** | XCircle | Dibatalkan |

### Icon Library

Tersedia 35+ icon dari Lucide icons yang dikategorikan:

| Kategori | Icon |
|----------|------|
| **Waktu** | Clock, Timer, Hourglass, CalendarClock |
| **Sukses** | CheckCircle2, CircleCheck, Check, BadgeCheck |
| **Proses** | ChefHat, Utensils, Flame, CookingPot, RefreshCw |
| **Paket** | Package, Box, Gift, Archive, PackageCheck |
| **Pengiriman** | Truck, Car, Bike, Send, Navigation |
| **Batal** | XCircle, X, Ban, CircleX, AlertCircle |
| **Lainnya** | ShoppingBag, ShoppingCart, Receipt, Star, Heart |

### Cara Mengubah Icon

1. Klik tombol **"Ubah"** di bawah icon status
2. Icon Picker modal akan muncul
3. Cari icon dengan search atau filter kategori
4. Klik icon yang diinginkan
5. Klik **"Pilih Icon"**
6. Klik **"Simpan Pengaturan"**

### Fitur Icon Picker

- **Search**: Cari icon berdasarkan nama
- **Category Filter**: Filter berdasarkan kategori
- **Preview**: Lihat icon terpilih sebelum konfirmasi
- **Reset**: Kembalikan ke default per status

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
| whatsapp_number | Required, max 20 chars |
| phone_country_code | Required, max 5 chars |
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
| `/admin/settings/upload-favicon` | POST | Upload favicon toko |

### Request Body (PATCH)

```json
{
  "store_name": "Nama Toko",
  "store_tagline": "Tagline",
  "store_address": "Alamat",
  "store_phone": "021-1234567",
  "whatsapp_number": "6281234567890",
  "phone_country_code": "ID",
  "operating_hours": {...},
  "delivery_areas": ["Area 1", "Area 2"],
  "delivery_fee": 10000,
  "minimum_order": 50000,
  "auto_cancel_enabled": true,
  "auto_cancel_minutes": 30,
  "whatsapp_template_confirmed": "Halo *{customer_name}*! ...",
  "whatsapp_template_preparing": "Halo *{customer_name}*! ...",
  "whatsapp_template_ready": "Halo *{customer_name}*! ...",
  "whatsapp_template_delivered": "Halo *{customer_name}*! ...",
  "whatsapp_template_cancelled": "Halo *{customer_name}*, ...",
  "timeline_icons": {
    "created": "Clock",
    "pending": "Clock",
    "confirmed": "CheckCircle2",
    "preparing": "ChefHat",
    "ready": "Package",
    "delivered": "Truck",
    "cancelled": "XCircle"
  }
}
```

### Response (Success)

```json
{
  "message": "Pengaturan berhasil disimpan."
}
```

---

## Security (OWASP Compliance)

### Template Validation

Template pesan WhatsApp divalidasi untuk mencegah injection attacks:

| Validasi | Deskripsi |
|----------|-----------|
| **Max Length** | Maksimal 2000 karakter per template |
| **XSS Prevention** | Block `<script>`, `javascript:`, event handlers |
| **Variable Validation** | Hanya variabel yang diizinkan yang dapat digunakan |
| **Pattern Detection** | Deteksi pola berbahaya (iframe, object, embed, etc.) |

### Icon Validation

Icon timeline divalidasi dengan whitelist:

| Validasi | Deskripsi |
|----------|-----------|
| **Whitelist** | Hanya 35+ icon dari Lucide yang diizinkan |
| **Max Length** | Nama icon maksimal 50 karakter |
| **Injection Prevention** | Mencegah component injection |

### Data Sanitization

Semua user data yang dimasukkan ke template di-sanitize:

| Sanitization | Deskripsi |
|--------------|-----------|
| **Null Bytes** | Dihapus untuk mencegah injection |
| **Control Characters** | Dihapus (kecuali newline, tab) |
| **Length Limit** | Maksimal 500 karakter per variabel |
| **Special Chars** | Escape backslash dan kurung kurawal |
