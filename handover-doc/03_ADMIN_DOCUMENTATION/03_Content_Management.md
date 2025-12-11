# Manajemen Konten

## Informasi Dokumen

| Atribut | Detail |
|---------|--------|
| **Nama Dokumen** | Panduan Manajemen Konten |
| **Developer** | Zulfikar Hidayatullah (+62 857-1583-8733) |
| **Versi** | 1.0.0 |

---

## Overview

Manajemen Konten pada Simple Store merupakan modul yang bertujuan untuk mengelola semua konten toko, yaitu: produk, kategori, dan media (gambar). Modul ini memungkinkan admin untuk melakukan operasi CRUD dengan interface yang user-friendly dan iOS-like design.

---

## Manajemen Produk

### URL Akses

| Halaman | URL |
|---------|-----|
| Daftar Produk | `/admin/products` |
| Tambah Produk | `/admin/products/create` |
| Edit Produk | `/admin/products/{id}/edit` |
| Detail Produk | `/admin/products/{id}` |

### Struktur Data Produk

| Field | Tipe | Required | Deskripsi |
|-------|------|:--------:|-----------|
| `name` | varchar | ✅ | Nama produk |
| `slug` | varchar | Auto | URL-friendly name (auto-generated) |
| `category_id` | integer | ✅ | ID kategori produk |
| `description` | text | ❌ | Deskripsi lengkap produk |
| `price` | numeric | ✅ | Harga dalam Rupiah |
| `stock` | integer | ✅ | Jumlah stok tersedia |
| `image` | varchar | ❌ | Path gambar produk |
| `is_active` | boolean | ✅ | Status aktif (default: true) |
| `is_featured` | boolean | ❌ | Produk unggulan (default: false) |

### Menambah Produk Baru

**URL**: `/admin/products/create`

**Step-by-step:**

1. **Navigasi ke Halaman**
   - Klik menu "Produk" di sidebar
   - Klik tombol "Tambah Produk" (atau FAB di mobile)

2. **Isi Form Produk**

   **Informasi Dasar:**
   - **Nama Produk*** - Nama yang akan ditampilkan
   - **Kategori*** - Pilih dari dropdown kategori aktif
   - **Harga*** - Masukkan dalam Rupiah (contoh: 25000)
   - **Stok*** - Jumlah stok tersedia

   **Detail Produk:**
   - **Deskripsi** - Penjelasan lengkap produk (opsional)

   **Media:**
   - **Gambar Produk** - Upload foto produk (JPG/PNG/WebP, max 5MB)

   **Status:**
   - **Aktif** - Toggle untuk menampilkan/sembunyikan produk
   - **Featured** - Toggle untuk menandai sebagai produk unggulan

3. **Simpan Produk**
   - Klik tombol "Simpan Produk"
   - Sistem akan memvalidasi dan menyimpan data
   - Redirect ke daftar produk dengan flash message sukses

### Mengedit Produk

**URL**: `/admin/products/{id}/edit`

**Step-by-step:**

1. Di halaman daftar produk, klik tombol "Edit" pada produk yang diinginkan
2. Form akan terisi dengan data existing
3. Ubah field yang diperlukan
4. Klik "Update Produk" untuk menyimpan perubahan

**Catatan:**
- Gambar lama akan tetap tersimpan jika tidak upload gambar baru
- Mengubah nama tidak akan mengubah slug (untuk menjaga URL)

### Menghapus Produk

**Proses hapus produk:**

1. Di halaman daftar produk, klik tombol "Hapus" pada produk
2. Dialog konfirmasi akan muncul dengan preview produk
3. Klik "Ya, Hapus Produk" untuk lanjut
4. Masukkan password admin untuk konfirmasi keamanan
5. Produk dihapus secara permanen

**Batasan:**
- Produk yang ada di pesanan aktif tidak dapat dihapus
- Gambar produk akan ikut terhapus dari storage

### Filter dan Pencarian Produk

**Fitur yang tersedia:**

| Filter | Deskripsi |
|--------|-----------|
| **Search** | Cari berdasarkan nama produk |
| **Kategori** | Filter berdasarkan kategori |
| **Status** | Filter aktif/nonaktif |
| **Reset** | Hapus semua filter |

**Sorting (Desktop):**
- Klik header kolom untuk sort ascending/descending
- Support sort: Nama, Kategori, Harga, Stok, Status

### Indikator Status Produk

| Status | Badge | Deskripsi |
|--------|-------|-----------|
| **Aktif** | 🟢 Hijau | Produk ditampilkan di storefront |
| **Nonaktif** | ⚪ Abu | Produk disembunyikan dari storefront |
| **Featured** | ⭐ Kuning | Produk unggulan dengan highlight |
| **Stok Tinggi** | 🟢 > 10 | Stok cukup |
| **Stok Rendah** | 🟡 1-10 | Perlu restok segera |
| **Habis** | 🔴 0 | Produk tidak bisa dipesan |

---

## Manajemen Kategori

### URL Akses

| Halaman | URL |
|---------|-----|
| Daftar Kategori | `/admin/categories` |
| Tambah Kategori | `/admin/categories/create` |
| Edit Kategori | `/admin/categories/{id}/edit` |

### Struktur Data Kategori

| Field | Tipe | Required | Deskripsi |
|-------|------|:--------:|-----------|
| `name` | varchar | ✅ | Nama kategori |
| `slug` | varchar | Auto | URL-friendly name |
| `description` | text | ❌ | Deskripsi kategori |
| `image` | varchar | ❌ | Path gambar kategori |
| `is_active` | boolean | ✅ | Status aktif (default: true) |
| `sort_order` | integer | ❌ | Urutan tampilan |

### Menambah Kategori Baru

**URL**: `/admin/categories/create`

**Step-by-step:**

1. Klik menu "Kategori" di sidebar
2. Klik tombol "Tambah Kategori"
3. Isi form:
   - **Nama Kategori*** - Nama yang akan ditampilkan
   - **Deskripsi** - Penjelasan kategori (opsional)
   - **Gambar** - Icon/gambar kategori (opsional)
   - **Status Aktif** - Toggle visibilitas
   - **Urutan** - Angka untuk pengurutan (opsional)
4. Klik "Simpan Kategori"

### Mengedit Kategori

**URL**: `/admin/categories/{id}/edit`

**Step-by-step:**

1. Di halaman daftar kategori, klik tombol "Edit"
2. Ubah field yang diperlukan
3. Klik "Update Kategori" untuk menyimpan

### Menghapus Kategori

**Batasan Penting:**
- ⚠️ Kategori dengan produk tidak dapat dihapus
- Pindahkan atau hapus semua produk terlebih dahulu

**Proses:**
1. Pastikan tidak ada produk dalam kategori
2. Klik tombol "Hapus" pada kategori
3. Konfirmasi penghapusan
4. Kategori dihapus dari database

### Informasi Product Count

Halaman daftar kategori menampilkan jumlah produk per kategori:
- Membantu admin mengetahui distribusi produk
- Kategori dengan product count > 0 tidak bisa dihapus

---

## Media Management

### Format yang Didukung

| Tipe | Format | Max Size |
|------|--------|----------|
| Gambar | JPG, JPEG, PNG, WebP | 5MB |

### Lokasi Penyimpanan

| Konten | Storage Path |
|--------|--------------|
| Produk | `storage/app/public/products/` |
| Kategori | `storage/app/public/categories/` |
| Logo Toko | `storage/app/public/branding/` |

### Upload Gambar Produk

**Requirements:**
- Format: JPG, PNG, atau WebP
- Ukuran maksimal: 5MB
- Resolusi rekomendasi: minimal 800x800px
- Aspek rasio: 1:1 (square) disarankan

**Proses Upload:**
1. Klik area upload atau tombol "Pilih Gambar"
2. Pilih file dari device
3. Preview gambar akan muncul
4. Gambar akan diupload saat form disubmit

**Image Processing:**
- Gambar secara otomatis di-optimize
- Konversi ke WebP untuk performa
- Resize proporsional jika terlalu besar

### Upload Logo Toko

**URL**: `/admin/settings` → Bagian Logo

**Requirements:**
- Format: JPG, PNG, atau WebP
- Ukuran maksimal: 2MB
- Resolusi rekomendasi: 200x200px

**Proses:**
1. Klik tombol "Upload Logo" atau area preview
2. Pilih file gambar
3. Gambar langsung diupload via AJAX
4. Preview update secara real-time
5. Klik "Simpan Pengaturan" untuk finalisasi

### Menghapus Media

**Gambar Produk:**
- Upload gambar baru akan mengganti gambar lama
- Gambar lama otomatis dihapus dari storage

**Logo Toko:**
- Klik "Hapus Logo" di halaman settings
- Upload logo baru sebagai pengganti

---

## Best Practices

### Naming Convention

**Produk:**
- Gunakan nama yang deskriptif dan jelas
- Hindari karakter khusus berlebihan
- Contoh baik: "Kopi Arabika Gayo 250gr"
- Contoh buruk: "***BEST SELLER*** KOPI!!! 🔥🔥🔥"

**Kategori:**
- Gunakan nama singkat dan jelas
- Maksimal 2-3 kata
- Contoh baik: "Minuman", "Makanan Ringan"
- Contoh buruk: "Kategori Untuk Semua Jenis Minuman Dingin dan Panas"

### Optimasi Gambar

1. **Sebelum Upload:**
   - Kompres gambar menggunakan tools online (TinyPNG, Squoosh)
   - Crop ke aspek rasio 1:1 jika memungkinkan
   - Pastikan pencahayaan dan kualitas baik

2. **Rekomendasi Resolusi:**
   - Produk: 800x800px - 1200x1200px
   - Kategori: 400x400px
   - Logo: 200x200px

### Manajemen Stok

1. **Update Berkala:**
   - Cek stok minimal seminggu sekali
   - Sync dengan stok fisik

2. **Alert Stok Rendah:**
   - Perhatikan indikator kuning (stok < 10)
   - Segera restok atau nonaktifkan produk

3. **Produk Habis:**
   - Nonaktifkan produk dengan stok 0
   - Update stok dan aktifkan kembali setelah restok

### SEO-Friendly Content

1. **Deskripsi Produk:**
   - Tulis minimal 100 kata
   - Sertakan keywords relevan
   - Jelaskan manfaat dan keunggulan

2. **Nama Produk:**
   - Sertakan kata kunci utama
   - Hindari keyword stuffing

---

## Troubleshooting

| Problem | Solusi |
|---------|--------|
| Upload gambar gagal | Cek ukuran file (<5MB) dan format (JPG/PNG/WebP) |
| Produk tidak muncul di storefront | Pastikan status "Aktif" dan kategori aktif |
| Kategori tidak bisa dihapus | Pindahkan/hapus produk dalam kategori terlebih dahulu |
| Gambar tidak tampil | Jalankan `php artisan storage:link` |
| Slug duplicate error | Ubah nama produk agar unik |

---

## API Endpoints

### Products

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/admin/products` | GET | Daftar produk (paginated) |
| `/admin/products/create` | GET | Form tambah produk |
| `/admin/products` | POST | Simpan produk baru |
| `/admin/products/{id}/edit` | GET | Form edit produk |
| `/admin/products/{id}` | PUT/PATCH | Update produk |
| `/admin/products/{id}` | DELETE | Hapus produk |

### Categories

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/admin/categories` | GET | Daftar kategori |
| `/admin/categories/create` | GET | Form tambah kategori |
| `/admin/categories` | POST | Simpan kategori baru |
| `/admin/categories/{id}/edit` | GET | Form edit kategori |
| `/admin/categories/{id}` | PUT/PATCH | Update kategori |
| `/admin/categories/{id}` | DELETE | Hapus kategori |
