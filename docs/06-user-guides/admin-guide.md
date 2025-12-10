# Admin Guide - F&B Web App

**Zulfikar Hidayatullah**  
**Version**: 1.4.0  
**Last Updated**: 2025-12-10

## Overview

Admin Guide merupakan panduan lengkap untuk administrator dalam mengelola aplikasi F&B Web App, yaitu: manajemen produk, kategori, monitoring pesanan, dan pengaturan toko. Panduan ini mencakup langkah-langkah detail untuk setiap operasi administratif yang tersedia di dalam sistem.

---

## Table of Contents

- [Akses Admin Panel](#akses-admin-panel)
- [Dashboard Overview](#dashboard-overview)
- [Manajemen Produk](#manajemen-produk)
  - [Melihat Daftar Produk](#melihat-daftar-produk)
  - [Menambah Produk Baru](#menambah-produk-baru)
  - [Mengedit Produk](#mengedit-produk)
  - [Menghapus Produk](#menghapus-produk)
- [Manajemen Kategori](#manajemen-kategori)
  - [Melihat Daftar Kategori](#melihat-daftar-kategori)
  - [Menambah Kategori Baru](#menambah-kategori-baru)
  - [Mengedit Kategori](#mengedit-kategori)
  - [Menghapus Kategori](#menghapus-kategori)
- [Manajemen Pesanan](#manajemen-pesanan)
  - [Melihat Daftar Pesanan](#melihat-daftar-pesanan)
  - [Melihat Detail Pesanan](#melihat-detail-pesanan)
  - [Update Status Pesanan](#update-status-pesanan)
  - [Komunikasi dengan Customer](#komunikasi-dengan-customer)
- [Pengaturan Toko](#pengaturan-toko)
  - [Informasi Toko](#informasi-toko)
  - [Branding Toko](#branding-toko)
  - [Pengaturan WhatsApp](#pengaturan-whatsapp)
  - [Jam Operasional](#jam-operasional)
  - [Pengaturan Delivery](#pengaturan-delivery)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

---

## Akses Admin Panel

### Login ke Admin Panel

1. Buka browser dan akses URL aplikasi
2. Klik menu **Login** di header
3. Masukkan kredensial admin:
   - **Email**: admin@test.com
   - **Password**: admin123
4. Klik tombol **Login**
5. Setelah berhasil login, Anda akan diarahkan ke Dashboard Admin

#### Kredensial Default

Untuk testing dan development, tersedia akun default berikut:

**Admin Account:**
- Email: `admin@test.com`
- Password: `admin123`
- Role: Admin (akses penuh)

**Customer Account:**
- Email: `customer@test.com`
- Password: `customer123`
- Role: Customer (akses terbatas)

### Navigasi Sidebar

Admin panel memiliki sidebar navigasi yang mencakup menu-menu berikut:

- **Dashboard**: Overview statistik dan pesanan terbaru
- **Pesanan**: Manajemen dan monitoring pesanan customer
- **Produk**: Manajemen produk F&B
- **Kategori**: Manajemen kategori produk

---

## Dashboard Overview

Dashboard Admin menampilkan overview statistik toko dengan metrics utama, yaitu:

### Metrics Utama

1. **Total Penjualan**
   - Menampilkan total revenue keseluruhan dalam format Rupiah
   - Update real-time berdasarkan pesanan yang masuk

2. **Orders Hari Ini**
   - Jumlah pesanan yang masuk hari ini
   - Berguna untuk monitoring performa harian

3. **Pending Orders**
   - Jumlah pesanan yang menunggu konfirmasi
   - Membutuhkan action segera dari admin

4. **Produk Aktif**
   - Total produk yang tersedia dan aktif di toko
   - Tidak termasuk produk yang di-nonaktifkan

### Pesanan Terbaru

Dashboard menampilkan 5 pesanan terbaru dengan informasi, antara lain:
- Nomor pesanan (order number)
- Nama customer
- Nomor telepon
- Total harga
- Status pesanan
- Jumlah item
- Waktu pemesanan

### Status Pesanan Breakdown

Menampilkan breakdown pesanan berdasarkan status untuk monitoring, yaitu:
- **Pending**: Pesanan menunggu konfirmasi
- **Confirmed**: Pesanan sudah dikonfirmasi
- **Processing**: Pesanan sedang diproses
- **Delivered**: Pesanan sudah dikirim/selesai
- **Cancelled**: Pesanan dibatalkan

### Quick Actions

Tombol akses cepat untuk navigasi ke halaman yang sering digunakan:
- **Kelola Produk**: Langsung ke halaman manajemen produk
- **Kelola Kategori**: Langsung ke halaman manajemen kategori

---

## Manajemen Produk

Fitur manajemen produk memungkinkan admin untuk mengelola semua produk F&B yang dijual di toko dengan operasi CRUD lengkap.

### Melihat Daftar Produk

#### Akses Halaman Produk

1. Dari sidebar, klik menu **Produk**
2. Halaman akan menampilkan tabel daftar produk dengan pagination

#### Kolom Tabel Produk

Tabel menampilkan informasi produk, antara lain:

- **Produk**: Gambar thumbnail, nama produk, dan badge "Featured" (jika produk unggulan)
- **Kategori**: Nama kategori produk
- **Harga**: Harga dalam format Rupiah
- **Stok**: Jumlah stok tersedia dengan color coding:
  - Hijau: Stok > 10
  - Kuning: Stok 1-10
  - Merah: Stok habis (0)
- **Status**: Badge status aktif/tidak aktif
- **Aksi**: Tombol Edit dan Hapus

#### Filter dan Pencarian

**Search Bar**
- Ketik nama produk di search bar
- Pencarian otomatis dengan debounce (300ms)
- Mencari berdasarkan nama dan deskripsi produk

**Filter Kategori**
- Pilih kategori dari dropdown filter
- Menampilkan produk dari kategori yang dipilih
- Pilih "Semua Kategori" untuk reset filter

**Filter Status**
- **Aktif**: Menampilkan produk yang aktif saja
- **Tidak Aktif**: Menampilkan produk yang dinonaktifkan
- **Semua Status**: Menampilkan semua produk

**Reset Filter**
- Klik tombol **Reset** untuk menghapus semua filter
- Mengembalikan tampilan ke semua produk

#### Pagination

- Default: 10 produk per halaman
- Navigasi: Tombol Previous/Next
- Informasi: "Menampilkan X - Y dari Z produk"

---

### Menambah Produk Baru

#### Langkah-langkah

1. Di halaman Produk, klik tombol **Tambah Produk** (pojok kanan atas)
2. Isi form dengan informasi produk yang diperlukan
3. Klik tombol **Simpan Produk**

#### Form Fields

**Informasi Produk** (Card pertama):

1. **Nama Produk** (Required)
   - Input nama produk yang akan ditampilkan
   - Maksimal 255 karakter
   - Contoh: "Nasi Goreng Spesial"

2. **Deskripsi** (Optional)
   - Textarea untuk deskripsi detail produk
   - Maksimal 5000 karakter
   - Mendukung line breaks

3. **Harga (Rp)** (Required)
   - Input harga produk dalam Rupiah
   - Hanya angka, tanpa separator
   - Minimal: 0
   - Maksimal: 999.999.999

4. **Stok** (Required)
   - Input jumlah stok produk
   - Hanya angka bulat
   - Minimal: 0
   - Maksimal: 999.999

5. **Kategori** (Required)
   - Dropdown pilihan kategori
   - Hanya kategori aktif yang ditampilkan
   - Pilih kategori yang sesuai dengan produk

**Gambar Produk** (Card kedua):

- **Image Upload** (Optional)
  - Klik atau drag file untuk upload
  - Format yang didukung: JPEG, PNG, JPG, WEBP
  - Ukuran maksimal: 2MB
  - Preview gambar akan ditampilkan setelah dipilih
  - Tombol X untuk menghapus gambar yang dipilih

**Status Produk** (Sidebar):

1. **Produk Aktif** (Checkbox)
   - Centang: Produk akan ditampilkan di toko
   - Tidak centang: Produk tersembunyi dari customer
   - Default: Centang (aktif)

2. **Produk Unggulan** (Checkbox)
   - Centang: Produk akan ditampilkan di bagian featured
   - Tidak centang: Produk biasa
   - Default: Tidak centang

#### Validasi Form

Sistem akan memvalidasi input dengan rules berikut:

- **Nama**: Wajib diisi, maksimal 255 karakter
- **Harga**: Wajib diisi, harus angka, minimal 0
- **Stok**: Wajib diisi, harus angka bulat, minimal 0
- **Kategori**: Wajib dipilih, harus kategori yang valid
- **Gambar**: Harus file gambar, format JPEG/PNG/JPG/WEBP, maksimal 2MB

Error message akan ditampilkan di bawah field yang bermasalah.

#### Setelah Submit

- Jika berhasil: Redirect ke halaman daftar produk dengan pesan sukses
- Jika gagal: Tetap di form dengan error messages

---

### Mengedit Produk

#### Langkah-langkah

1. Di halaman Produk, cari produk yang ingin diedit
2. Klik tombol **Edit** (icon pensil) pada row produk
3. Form edit akan terbuka dengan data produk yang sudah terisi
4. Ubah informasi yang diperlukan
5. Klik tombol **Simpan Perubahan**

#### Form Edit Produk

Form edit sama dengan form tambah, dengan perbedaan:

- **Data Pre-filled**: Semua field sudah terisi dengan data produk saat ini
- **Gambar Existing**: Gambar saat ini ditampilkan (jika ada)
- **Gambar Baru**: Optional - upload hanya jika ingin mengganti gambar
  - Gambar lama akan dihapus otomatis saat upload gambar baru
  - Jika tidak upload, gambar lama tetap digunakan

#### Slug Auto-generation

- Slug dibuat otomatis dari nama produk
- Format: lowercase dengan dash (-)
- Contoh: "Nasi Goreng Spesial" → "nasi-goreng-spesial"
- Slug akan diupdate otomatis jika nama produk berubah

---

### Menghapus Produk

#### Langkah-langkah

1. Di halaman Produk, cari produk yang ingin dihapus
2. Klik tombol **Hapus** (icon trash) pada row produk
3. Dialog preview akan muncul dengan informasi produk (gambar, nama, kategori, harga, stok)
4. Klik tombol **Ya, Hapus Produk** untuk melanjutkan
5. Dialog verifikasi password akan muncul
6. Masukkan password admin Anda
7. Klik tombol **Hapus Permanen** untuk konfirmasi

#### Konfirmasi Hapus (2 Tahap)

**Tahap 1 - Preview Dialog:**
- Gambar produk
- Nama produk
- Kategori
- Harga dan stok
- Peringatan bahwa tindakan tidak dapat dibatalkan

**Tahap 2 - Password Verification:**
- Input password admin
- Sistem akan memverifikasi password sebelum eksekusi delete

#### Constraint Check

Sistem akan melakukan validasi sebelum menghapus produk:

**Tidak Dapat Dihapus Jika:**
- Produk ada di pesanan dengan status: pending, processing, atau confirmed
- Error message: "Produk tidak dapat dihapus karena masih ada di X pesanan aktif."

**Dapat Dihapus Jika:**
- Produk tidak ada di pesanan aktif
- Atau hanya ada di pesanan dengan status: delivered atau cancelled

#### Setelah Hapus

- **Berhasil**: Produk dihapus dari database, gambar dihapus dari storage
- **Gagal**: Error message ditampilkan, produk tetap ada

---

## Manajemen Kategori

Fitur manajemen kategori memungkinkan admin untuk mengelola kategori produk dengan operasi CRUD lengkap dan pengaturan urutan tampilan.

### Melihat Daftar Kategori

#### Akses Halaman Kategori

1. Dari sidebar, klik menu **Kategori**
2. Halaman akan menampilkan tabel daftar kategori

#### Kolom Tabel Kategori

Tabel menampilkan informasi kategori, antara lain:

- **Kategori**: Icon/gambar thumbnail dan nama kategori
- **Deskripsi**: Deskripsi singkat kategori (jika ada)
- **Jumlah Produk**: Badge dengan icon package menampilkan jumlah produk di kategori
- **Urutan**: Nilai sort_order untuk pengurutan tampilan
- **Status**: Badge status aktif/tidak aktif
- **Aksi**: Tombol Edit dan Hapus

#### Sort Order

Kategori diurutkan berdasarkan `sort_order` (ascending), kemudian nama:
- Angka lebih kecil akan ditampilkan lebih dulu
- Berguna untuk mengatur urutan tampilan kategori di customer page

---

### Menambah Kategori Baru

#### Metode 1: Tombol Tambah Kategori

1. Di halaman Kategori, klik tombol **Tambah Kategori**
2. Modal form akan muncul
3. Isi form dengan informasi kategori
4. Klik tombol **Simpan**

#### Metode 2: Halaman Create (Opsional)

Tersedia juga halaman dedicated untuk create kategori (URL: `/admin/categories/create`)

#### Form Fields

**Modal Form mencakup fields berikut:**

1. **Nama Kategori** (Required)
   - Input nama kategori
   - Maksimal 255 karakter
   - Harus unique (tidak boleh duplikat)
   - Contoh: "Makanan Berat", "Minuman Dingin"

2. **Deskripsi** (Optional)
   - Textarea untuk deskripsi kategori
   - Maksimal 1000 karakter
   - Contoh: "Kategori untuk makanan utama seperti nasi, mie, dan pasta"

3. **Urutan** (Optional)
   - Input angka untuk sort order
   - Minimal: 0
   - Jika tidak diisi, akan diset otomatis ke max + 1
   - Angka lebih kecil = tampil lebih dulu

4. **Gambar** (Optional)
   - Upload gambar kategori
   - Format: JPEG, PNG, JPG, WEBP
   - Maksimal: 2MB
   - Preview ditampilkan setelah upload

5. **Kategori Aktif** (Checkbox)
   - Centang: Kategori aktif dan ditampilkan
   - Tidak centang: Kategori tersembunyi
   - Default: Centang

#### Validasi Form

- **Nama**: Wajib diisi, maksimal 255 karakter, harus unique
- **Deskripsi**: Optional, maksimal 1000 karakter
- **Urutan**: Optional, harus angka bulat, minimal 0
- **Gambar**: Optional, harus file gambar valid, maksimal 2MB

#### Auto Sort Order

Jika field Urutan tidak diisi, sistem akan:
1. Mencari sort_order maksimal dari kategori existing
2. Menambah 1
3. Menggunakan nilai tersebut sebagai sort_order

Contoh: Jika kategori terakhir memiliki sort_order = 5, kategori baru akan mendapat sort_order = 6

---

### Mengedit Kategori

#### Metode 1: Modal Edit

1. Di halaman Kategori, klik tombol **Edit** (icon pensil)
2. Modal form akan muncul dengan data pre-filled
3. Ubah informasi yang diperlukan
4. Klik tombol **Simpan**

#### Metode 2: Halaman Edit (Opsional)

Tersedia juga halaman dedicated untuk edit kategori

#### Form Edit Kategori

Form edit sama dengan form tambah, dengan fitur tambahan:

- **Data Pre-filled**: Semua field sudah terisi dengan data kategori
- **Gambar Existing**: Ditampilkan jika ada
- **Unique Name Validation**: Nama boleh sama dengan nama kategori saat ini (sendiri)
- **Product Count Display**: Menampilkan jumlah produk di kategori (di halaman edit)

#### Update Gambar

- Upload gambar baru untuk mengganti gambar lama
- Gambar lama akan dihapus otomatis
- Jika tidak upload, gambar lama tetap digunakan

---

### Menghapus Kategori

#### Langkah-langkah

1. Di halaman Kategori, cari kategori yang ingin dihapus
2. Klik tombol **Hapus** (icon trash) - tombol akan disabled jika kategori memiliki produk
3. Dialog preview akan muncul dengan informasi kategori (gambar, nama, deskripsi, jumlah produk)
4. Klik tombol **Ya, Hapus Kategori** untuk melanjutkan
5. Dialog verifikasi password akan muncul
6. Masukkan password admin Anda
7. Klik tombol **Hapus Permanen** untuk konfirmasi

#### Konfirmasi Hapus (2 Tahap)

**Tahap 1 - Preview Dialog:**
- Gambar kategori
- Nama kategori
- Deskripsi (jika ada)
- Jumlah produk dalam kategori
- Peringatan bahwa tindakan tidak dapat dibatalkan

**Tahap 2 - Password Verification:**
- Input password admin
- Sistem akan memverifikasi password sebelum eksekusi delete

#### Constraint Check

Sistem akan melakukan validasi sebelum menghapus kategori:

**Tidak Dapat Dihapus Jika:**
- Kategori masih memiliki produk (products_count > 0)
- Error message: "Kategori tidak dapat dihapus karena masih memiliki X produk."
- Tombol hapus akan disabled jika ada produk

**Dapat Dihapus Jika:**
- Kategori tidak memiliki produk sama sekali (products_count = 0)

#### Rekomendasi Sebelum Hapus

Sebelum menghapus kategori dengan produk:
1. Pindahkan semua produk ke kategori lain, atau
2. Hapus produk-produk di kategori tersebut terlebih dahulu

#### Setelah Hapus

- **Berhasil**: Kategori dihapus dari database, gambar dihapus dari storage
- **Gagal**: Error message ditampilkan, kategori tetap ada

---

## Manajemen Pesanan

Fitur manajemen pesanan memungkinkan admin untuk melihat, memantau, dan mengupdate status pesanan customer dengan tracking timestamps yang lengkap.

### Melihat Daftar Pesanan

#### Akses Halaman Pesanan

1. Dari sidebar, klik menu **Pesanan**
2. Halaman akan menampilkan tabel daftar pesanan dengan pagination

#### Kolom Tabel Pesanan

Tabel menampilkan informasi pesanan, antara lain:

- **No. Pesanan**: Order number unik (format: ORD-YYYYMMDD-XXXXX), clickable ke detail
- **Customer**: Nama dan nomor telepon customer (clickable ke WhatsApp)
- **Total**: Total harga pesanan dalam format Rupiah
- **Items**: Badge jumlah item dalam pesanan
- **Status**: Badge status dengan color coding:
  - Kuning: Menunggu (Pending)
  - Biru: Dikonfirmasi (Confirmed)
  - Ungu: Diproses (Preparing)
  - Cyan: Siap (Ready)
  - Hijau: Dikirim (Delivered)
  - Merah: Dibatalkan (Cancelled)
- **Tanggal**: Waktu pemesanan
- **Aksi**: Tombol Detail untuk melihat informasi lengkap

#### Filter dan Pencarian

**Search Bar**
- Ketik untuk mencari berdasarkan:
  - Order number
  - Nama customer
  - Nomor telepon customer
- Pencarian otomatis dengan debounce (300ms)

**Filter Status**
- Pilih status dari dropdown filter
- Menampilkan pesanan dengan status yang dipilih
- Pilih kosong untuk semua status

**Filter Tanggal**
- **Tanggal Mulai**: Filter pesanan dari tanggal tertentu
- **Tanggal Akhir**: Filter pesanan sampai tanggal tertentu
- Berguna untuk melihat pesanan dalam rentang waktu tertentu

**Reset Filter**
- Klik tombol **Reset** untuk menghapus semua filter
- Mengembalikan tampilan ke semua pesanan

#### Pagination

- Default: 10 pesanan per halaman
- Navigasi: Tombol Previous/Next
- Informasi: "Menampilkan X - Y dari Z pesanan"

---

### Melihat Detail Pesanan

#### Akses Detail Pesanan

1. Di halaman Pesanan, cari pesanan yang ingin dilihat
2. Klik order number atau tombol **Detail** pada row pesanan
3. Halaman detail akan menampilkan informasi lengkap

#### Informasi yang Ditampilkan

**Card Informasi Customer:**
- Nama customer
- Nomor telepon (clickable ke WhatsApp)
- Alamat lengkap
- Catatan pesanan (jika ada)

**Card Detail Pesanan:**
- Tabel item pesanan dengan kolom:
  - Produk: Nama produk dan catatan item (jika ada)
  - Harga: Harga satuan
  - Qty: Jumlah item
  - Subtotal: Harga × Quantity
- Order Summary:
  - Subtotal semua item
  - Ongkos kirim
  - **Total** keseluruhan

**Card Timeline Status:**
- Menampilkan riwayat perubahan status dengan timestamps:
  - Pesanan Dibuat (created_at)
  - Dikonfirmasi (confirmed_at)
  - Sedang Diproses (preparing_at)
  - Siap Dikirim (ready_at)
  - Terkirim (delivered_at)
  - Dibatalkan (cancelled_at) - termasuk alasan pembatalan

---

### Update Status Pesanan

#### Flow Status Pesanan

Status pesanan mengikuti alur sebagai berikut:

1. **Menunggu (Pending)** - Pesanan baru masuk, menunggu konfirmasi admin
2. **Dikonfirmasi (Confirmed)** - Admin sudah konfirmasi pesanan
3. **Diproses (Preparing)** - Pesanan sedang diproses/dimasak
4. **Siap (Ready)** - Pesanan siap untuk dikirim/diambil
5. **Dikirim (Delivered)** - Pesanan sudah dikirim ke customer
6. **Dibatalkan (Cancelled)** - Pesanan dibatalkan (memerlukan alasan)

#### Langkah Update Status

1. Di halaman Detail Pesanan, lihat card **Update Status**
2. Pilih status baru dari dropdown
3. Jika memilih **Dibatalkan**, field alasan pembatalan akan muncul (wajib diisi)
4. Klik tombol **Update Status**
5. Dialog konfirmasi akan muncul
6. Klik **Ya, Update** untuk konfirmasi atau **Batal** untuk membatalkan

#### Timestamp Logging

Setiap perubahan status akan dicatat dengan timestamp otomatis:
- `confirmed_at` - Waktu dikonfirmasi
- `preparing_at` - Waktu mulai diproses
- `ready_at` - Waktu pesanan siap
- `delivered_at` - Waktu dikirim
- `cancelled_at` - Waktu dibatalkan

#### Pembatalan Pesanan

Ketika membatalkan pesanan:
- **Alasan pembatalan wajib diisi** (maksimal 1000 karakter)
- Contoh alasan: "Stok habis", "Customer membatalkan", "Alamat tidak valid"
- Alasan akan ditampilkan di timeline dan detail pesanan
- **Tindakan pembatalan tidak dapat dibatalkan**

---

### Komunikasi dengan Customer

#### WhatsApp Integration

Admin dapat langsung menghubungi customer melalui WhatsApp:

1. Di halaman daftar pesanan, klik nomor telepon customer
2. Atau di halaman detail, klik nomor telepon di card Customer Info
3. WhatsApp akan terbuka dengan chat baru ke customer
4. Gunakan untuk:
   - Konfirmasi pesanan
   - Klarifikasi alamat
   - Informasi estimasi pengiriman
   - Notifikasi perubahan status

---

## Pengaturan Toko

Fitur pengaturan toko memungkinkan admin untuk mengkonfigurasi informasi dasar toko, nomor WhatsApp bisnis, jam operasional, dan pengaturan delivery yang akan digunakan di seluruh sistem aplikasi.

### Akses Halaman Pengaturan

1. Dari sidebar, klik menu **Pengaturan**
2. Halaman akan menampilkan form pengaturan toko dengan beberapa section

---

### Informasi Toko

Section ini digunakan untuk mengatur informasi dasar toko yang akan ditampilkan ke customer.

#### Form Fields

1. **Nama Toko** (Required)
   - Input nama toko yang akan ditampilkan
   - Maksimal 255 karakter
   - Contoh: "Warung Makan Enak"

2. **Alamat Toko** (Optional)
   - Textarea untuk alamat lengkap toko
   - Maksimal 1000 karakter
   - Digunakan untuk informasi di halaman checkout

3. **Nomor Telepon Toko** (Optional)
   - Input nomor telepon toko
   - Maksimal 50 karakter
   - Contoh: "021-1234567"

---

### Branding Toko

Section ini digunakan untuk mengatur identitas visual toko yang akan ditampilkan di seluruh halaman customer.

#### Form Fields

1. **Logo Toko** (Optional)
   - Upload gambar logo toko
   - Format yang didukung: JPEG, PNG, JPG, WEBP
   - Ukuran maksimal: 2MB
   - Logo akan ditampilkan di:
     - Header halaman customer (Home, Cart, Checkout, dll)
     - Footer halaman customer
     - Admin sidebar
   - Jika tidak ada logo, sistem akan menampilkan icon ShoppingBag default

2. **Tagline Toko** (Optional)
   - Input tagline/slogan toko
   - Maksimal 255 karakter
   - Contoh: "Premium Quality Products", "Makanan Enak Harga Terjangkau"
   - Tagline ditampilkan di bawah nama toko di header dan footer

#### Mengelola Logo Toko

**Upload Logo Baru:**
1. Klik area upload atau drag file logo ke area tersebut
2. Preview logo akan muncul setelah file dipilih
3. Klik **Simpan Pengaturan** untuk menyimpan

**Mengganti Logo:**
1. Upload logo baru akan otomatis mengganti logo lama
2. Logo lama akan dihapus dari storage

**Menghapus Logo:**
1. Klik tombol **X** pada preview logo
2. Klik **Simpan Pengaturan** untuk menyimpan perubahan
3. Sistem akan kembali menampilkan icon default

#### Tips Logo Toko

- **Resolusi Optimal:** 200x200 pixels atau lebih tinggi
- **Aspect Ratio:** 1:1 (square) untuk tampilan terbaik
- **Background:** Gunakan background transparan (PNG) untuk fleksibilitas
- **Format Terbaik:** PNG untuk logo dengan transparansi, WEBP untuk ukuran file kecil

#### Dampak Branding

Perubahan branding akan langsung terlihat di:
- Halaman Home (header dan footer)
- Halaman Cart (header dan footer)
- Halaman Checkout (header dan footer)
- Halaman Detail Produk (header dan footer)
- Halaman Order Success (header dan footer)
- Halaman Account/Profile (header dan footer)
- Halaman Register (deskripsi)
- Admin Sidebar (logo dan nama toko)

---

### Pengaturan WhatsApp

Section ini digunakan untuk mengkonfigurasi nomor WhatsApp bisnis yang akan menerima pesanan dari customer.

#### Form Fields

**Nomor WhatsApp Bisnis** (Required)
- Input nomor WhatsApp dalam format internasional
- Tanpa tanda + di depan
- Contoh: "6281234567890" (bukan "+6281234567890")

#### Penjelasan Format Nomor

Nomor WhatsApp harus dalam format berikut:
- Kode negara Indonesia: 62
- Nomor tanpa angka 0 di depan
- Contoh: 081234567890 → 6281234567890

---

### Jam Operasional

Section ini digunakan untuk mengatur jam buka dan tutup toko untuk setiap hari dalam seminggu.

#### Konfigurasi Per Hari

Untuk setiap hari (Senin - Minggu), tersedia pengaturan berikut:

1. **Status Buka/Tutup** (Checkbox)
   - Centang: Toko buka pada hari tersebut
   - Tidak centang: Toko tutup pada hari tersebut

2. **Jam Buka** (Time Input)
   - Input waktu buka toko
   - Format: HH:MM (24 jam)
   - Contoh: "08:00"

3. **Jam Tutup** (Time Input)
   - Input waktu tutup toko
   - Format: HH:MM (24 jam)
   - Contoh: "21:00"

#### Contoh Konfigurasi

| Hari | Status | Jam Buka | Jam Tutup |
|------|--------|----------|-----------|
| Senin | Buka | 08:00 | 21:00 |
| Selasa | Buka | 08:00 | 21:00 |
| Rabu | Buka | 08:00 | 21:00 |
| Kamis | Buka | 08:00 | 21:00 |
| Jumat | Buka | 08:00 | 21:00 |
| Sabtu | Buka | 09:00 | 22:00 |
| Minggu | Tutup | - | - |

---

### Pengaturan Delivery

Section ini digunakan untuk mengatur biaya pengiriman, minimum order, dan area pengiriman yang dilayani.

#### Form Fields

1. **Biaya Pengiriman (Rp)** (Required)
   - Input biaya pengiriman dalam Rupiah
   - Hanya angka, tanpa separator
   - Minimal: 0 (gratis ongkir)
   - Contoh: 15000

2. **Minimum Order (Rp)** (Required)
   - Input minimum total order
   - Hanya angka, tanpa separator
   - Minimal: 0 (tidak ada minimum)
   - Contoh: 50000

3. **Area Pengiriman** (Optional)
   - Daftar area yang dilayani pengiriman
   - Klik tombol **+** untuk menambah area baru
   - Klik tombol **X** pada badge untuk menghapus area
   - Contoh area: "Jakarta Selatan", "Jakarta Pusat", "Bekasi"

#### Menambah Area Pengiriman

1. Ketik nama area di input field
2. Tekan **Enter** atau klik tombol **+**
3. Area akan ditambahkan sebagai badge

#### Menghapus Area Pengiriman

1. Klik tombol **X** pada badge area yang ingin dihapus
2. Area akan langsung terhapus dari daftar

---

### Menyimpan Pengaturan

Setelah mengisi atau mengubah pengaturan:

1. Review semua field yang telah diisi
2. Klik tombol **Simpan Pengaturan**
3. Tunggu proses penyimpanan
4. Pesan sukses akan muncul jika berhasil

#### Validasi

Sistem akan melakukan validasi sebelum menyimpan:

- **Nama Toko**: Wajib diisi, maksimal 255 karakter
- **Alamat Toko**: Maksimal 1000 karakter
- **Nomor Telepon**: Maksimal 50 karakter
- **WhatsApp**: Wajib diisi, maksimal 20 karakter
- **Jam Operasional**: Wajib diisi untuk semua hari
- **Biaya Pengiriman**: Wajib diisi, tidak boleh negatif
- **Minimum Order**: Wajib diisi, tidak boleh negatif

Error message akan ditampilkan di bawah field yang bermasalah jika validasi gagal.

---

## Best Practices

### Manajemen Produk

1. **Gunakan Gambar Berkualitas**
   - Upload gambar produk yang jelas dan menarik
   - Resolusi minimal: 400x400 pixels
   - Pastikan ukuran file tidak melebihi 2MB

2. **Deskripsi Produk yang Lengkap**
   - Tulis deskripsi yang informatif dan menarik
   - Sertakan informasi penting (bahan, ukuran porsi, tingkat kepedasan, dll)
   - Gunakan line breaks untuk keterbacaan

3. **Update Stok Secara Rutin**
   - Monitor dan update stok produk secara berkala
   - Set stok ke 0 untuk produk yang habis
   - Atau nonaktifkan produk jika temporary unavailable

4. **Status Produk**
   - Gunakan status "Tidak Aktif" untuk produk seasonal
   - Jangan hapus produk yang pernah dipesan
   - Gunakan fitur "Featured" untuk produk unggulan/promosi

5. **Kategori yang Tepat**
   - Pastikan produk berada di kategori yang sesuai
   - Konsisten dalam pengelompokan produk
   - Hindari kategori terlalu spesifik atau terlalu umum

### Manajemen Kategori

1. **Nama Kategori yang Jelas**
   - Gunakan nama yang mudah dipahami customer
   - Singkat namun deskriptif
   - Contoh: "Makanan Berat", "Minuman Dingin", "Snack"

2. **Sort Order yang Logis**
   - Urutkan berdasarkan popularitas atau frekuensi order
   - Kategori utama di urutan awal (sort_order kecil)
   - Contoh urutan: Makanan (1), Minuman (2), Snack (3), Dessert (4)

3. **Gambar Kategori**
   - Upload gambar representatif untuk setiap kategori
   - Gunakan style yang konsisten
   - Opsional namun meningkatkan user experience

4. **Deskripsi Kategori**
   - Tulis deskripsi singkat untuk clarity
   - Bantu customer memahami isi kategori
   - Contoh: "Makanan utama untuk kenyang dan berenergi"

### Manajemen Pesanan

1. **Respon Cepat Pesanan Baru**
   - Monitor pending orders secara berkala
   - Konfirmasi pesanan dalam waktu maksimal 15 menit
   - Hubungi customer jika ada klarifikasi yang diperlukan

2. **Update Status Tepat Waktu**
   - Update status sesuai progress aktual
   - Jangan skip tahapan status (misal: langsung dari Pending ke Ready)
   - Timestamp membantu tracking SLA

3. **Pembatalan dengan Bijak**
   - Tulis alasan pembatalan yang jelas dan sopan
   - Hubungi customer sebelum membatalkan
   - Tawarkan alternatif jika memungkinkan

4. **Komunikasi Proaktif**
   - Informasikan customer jika ada delay
   - Konfirmasi alamat untuk pesanan dengan nilai besar
   - Follow up setelah delivered untuk feedback

### Pengaturan Toko

1. **Update Informasi Secara Berkala**
   - Perbarui informasi toko jika ada perubahan alamat atau kontak
   - Pastikan nomor WhatsApp selalu aktif dan responsive
   - Review jam operasional jika ada perubahan jadwal

2. **Konfigurasi Delivery yang Tepat**
   - Tentukan biaya pengiriman yang kompetitif namun mengcover operasional
   - Set minimum order yang reasonable untuk customer
   - Update area pengiriman sesuai kapasitas delivery

3. **Monitoring Jam Operasional**
   - Tutup toko pada hari libur dengan mengubah status ke "Tutup"
   - Sesuaikan jam buka/tutup dengan kondisi aktual toko
   - Informasikan customer jika ada perubahan jam operasional mendadak

### Operasional Harian

1. **Monitoring Dashboard**
   - Cek dashboard setiap pagi untuk overview pesanan
   - Perhatikan pending orders yang perlu dikonfirmasi
   - Monitor stok produk yang menipis

2. **Backup Data**
   - Pastikan backup database dilakukan rutin
   - Simpan gambar produk di tempat yang aman
   - Dokumentasikan perubahan penting

3. **Validasi Data**
   - Periksa kembali data sebelum submit
   - Pastikan harga dan stok akurat
   - Verifikasi gambar yang diupload

---

## Troubleshooting

### Masalah Upload Gambar

**Problem**: Error "File size too large"  
**Solution**: 
- Compress gambar sebelum upload
- Gunakan tool seperti TinyPNG atau Squoosh
- Target ukuran: < 500KB untuk performa optimal

**Problem**: Error "Invalid file type"  
**Solution**:
- Pastikan file berformat: JPEG, PNG, JPG, atau WEBP
- Convert format lain ke format yang didukung
- Gunakan tool online converter jika diperlukan

**Problem**: Gambar tidak muncul setelah upload  
**Solution**:
- Refresh halaman browser
- Clear browser cache
- Cek koneksi internet
- Hubungi developer jika masalah persisten

### Masalah Form Submission

**Problem**: Error "The name has already been taken"  
**Solution**:
- Gunakan nama yang berbeda (untuk kategori)
- Cek daftar kategori existing terlebih dahulu
- Perhatikan case-sensitive validation

**Problem**: Validasi error tidak jelas  
**Solution**:
- Scroll ke field yang error (ditandai border merah)
- Baca error message di bawah field
- Perbaiki sesuai requirement yang disebutkan

**Problem**: Form tidak ter-submit  
**Solution**:
- Pastikan semua required fields terisi
- Cek koneksi internet
- Coba refresh halaman dan input ulang
- Hubungi developer jika masalah berlanjut

### Masalah Hapus Data

**Problem**: Tidak bisa menghapus produk  
**Solution**:
- Cek error message untuk detail
- Jika produk di order aktif, tunggu hingga order selesai
- Atau nonaktifkan produk sebagai alternatif

**Problem**: Tidak bisa menghapus kategori  
**Solution**:
- Pindahkan semua produk ke kategori lain terlebih dahulu
- Atau hapus produk di kategori tersebut
- Kategori hanya bisa dihapus jika kosong

### Masalah Performa

**Problem**: Halaman loading lambat  
**Solution**:
- Clear browser cache
- Pastikan koneksi internet stabil
- Tutup tab browser yang tidak digunakan
- Gunakan browser modern (Chrome, Firefox, Edge)

**Problem**: Search/filter tidak responsif  
**Solution**:
- Tunggu beberapa detik (ada debounce 300ms)
- Refresh halaman jika tidak berfungsi
- Clear cache dan cookies

### Masalah Pesanan

**Problem**: Pesanan tidak muncul di daftar  
**Solution**:
- Cek filter yang aktif (status, tanggal)
- Reset semua filter untuk melihat semua pesanan
- Pastikan pesanan sudah berhasil disimpan dari sisi customer

**Problem**: Tidak bisa update status pesanan  
**Solution**:
- Pastikan sudah login dengan akun admin
- Cek koneksi internet
- Refresh halaman dan coba lagi
- Hubungi developer jika masalah persisten

**Problem**: Status tidak berubah setelah update  
**Solution**:
- Tunggu beberapa detik untuk proses
- Refresh halaman untuk melihat perubahan
- Cek apakah ada error message yang muncul
- Pastikan status yang dipilih valid

**Problem**: Tidak bisa membatalkan pesanan  
**Solution**:
- Pastikan alasan pembatalan sudah diisi
- Alasan minimal harus ada isi (tidak boleh kosong)
- Maksimal 1000 karakter untuk alasan
- Refresh dan coba lagi jika form tidak responsif

**Problem**: WhatsApp tidak terbuka saat klik nomor telepon  
**Solution**:
- Pastikan format nomor telepon benar
- Cek apakah browser memblokir popup
- Coba buka manual dengan mengetik wa.me/[nomor]
- Gunakan aplikasi WhatsApp desktop jika ada

### Masalah Branding Toko

**Problem**: Logo tidak muncul setelah upload  
**Solution**:
- Pastikan file berformat: JPEG, PNG, JPG, atau WEBP
- Cek ukuran file tidak melebihi 2MB
- Jalankan `php artisan storage:link` jika belum
- Clear browser cache dan refresh halaman

**Problem**: Logo terpotong atau tidak proporsional  
**Solution**:
- Gunakan gambar dengan aspect ratio 1:1 (square)
- Resolusi minimal 200x200 pixels
- Hindari logo dengan banyak detail kecil

**Problem**: Tagline tidak muncul di halaman customer  
**Solution**:
- Pastikan tagline sudah disimpan di pengaturan
- Refresh halaman customer (Ctrl+F5)
- Tagline hanya muncul di layar desktop (hidden di mobile)

---

### Masalah Pengaturan Toko

**Problem**: Pengaturan tidak tersimpan  
**Solution**:
- Pastikan semua field required sudah diisi
- Cek validasi error di bawah masing-masing field
- Pastikan format nomor WhatsApp benar (tanpa tanda +)
- Refresh halaman dan coba lagi

**Problem**: Nomor WhatsApp tidak berfungsi di checkout  
**Solution**:
- Pastikan format nomor tanpa tanda + di depan
- Gunakan format: 6281234567890 (kode negara 62)
- Hindari spasi atau karakter khusus
- Test dengan klik nomor WhatsApp di halaman pesanan

**Problem**: Jam operasional tidak sesuai  
**Solution**:
- Pastikan checkbox "Buka" dicentang untuk hari yang dimaksud
- Cek apakah jam buka lebih kecil dari jam tutup
- Format waktu harus HH:MM (24 jam)
- Simpan ulang pengaturan setelah perubahan

**Problem**: Area pengiriman tidak bisa ditambah  
**Solution**:
- Pastikan nama area tidak kosong
- Tekan Enter atau klik tombol + setelah mengetik
- Cek apakah area sudah ada dalam daftar (tidak boleh duplikat)

---

## Kontak Support

Jika mengalami masalah yang tidak dapat diselesaikan dengan panduan ini, silakan hubungi:

**Developer/Technical Support:**
- Email: support@fbwebapp.com
- WhatsApp: +62-XXX-XXXX-XXXX

**Jam Operasional:**
- Senin - Jumat: 09:00 - 17:00 WIB
- Sabtu: 09:00 - 14:00 WIB
- Minggu: Closed

---

## Changelog

### Version 1.4.0 (2025-12-10)
- Tambah password confirmation untuk delete produk
- Tambah password confirmation untuk delete kategori
- Preview dialog sebelum delete menampilkan image dan info item
- Design Categories Index disesuaikan dengan Orders Index
- Badge styling menggunakan `admin-badge` classes yang konsisten
- Tambah `admin-badge--muted` untuk status nonaktif

### Version 1.3.0 (2025-12-10)
- Tambah dokumentasi branding toko (logo, tagline)
- Upload dan manajemen logo toko
- Tagline/slogan toko configuration
- Dampak branding di halaman customer
- Tips optimasi logo toko
- Update troubleshooting masalah branding

### Version 1.2.0 (2025-11-26)
- Tambah dokumentasi lengkap pengaturan toko
- Informasi toko (nama, alamat, telepon)
- Pengaturan WhatsApp bisnis
- Jam operasional per hari
- Pengaturan delivery (biaya, minimum order, area)
- Tambah best practices pengaturan toko
- Tambah troubleshooting masalah pengaturan

### Version 1.1.0 (2025-11-26)
- Tambah dokumentasi lengkap manajemen pesanan
- Update sidebar navigation dengan menu Pesanan
- Tambah best practices manajemen pesanan
- Tambah troubleshooting masalah pesanan

### Version 1.0.0 (2025-11-26)
- Initial release admin guide
- Dokumentasi lengkap manajemen produk
- Dokumentasi lengkap manajemen kategori
- Best practices dan troubleshooting

---

**© 2025 Zulfikar Hidayatullah. All Rights Reserved.**

