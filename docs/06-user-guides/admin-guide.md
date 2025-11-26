# Admin Guide - F&B Web App

**Zulfikar Hidayatullah**  
**Version**: 1.0.0  
**Last Updated**: 2025-11-26

## Overview

Admin Guide merupakan panduan lengkap untuk administrator dalam mengelola aplikasi F&B Web App, yaitu: manajemen produk, kategori, dan monitoring pesanan. Panduan ini mencakup langkah-langkah detail untuk setiap operasi administratif yang tersedia di dalam sistem.

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
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

---

## Akses Admin Panel

### Login ke Admin Panel

1. Buka browser dan akses URL aplikasi
2. Klik menu **Login** di header
3. Masukkan kredensial admin:
   - **Email**: email terdaftar sebagai admin
   - **Password**: password akun admin
4. Klik tombol **Login**
5. Setelah berhasil login, Anda akan diarahkan ke Dashboard Admin

### Navigasi Sidebar

Admin panel memiliki sidebar navigasi yang mencakup menu-menu berikut:

- **Dashboard**: Overview statistik dan pesanan terbaru
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
3. Dialog konfirmasi akan muncul
4. Baca konfirmasi dengan seksama
5. Klik tombol **Hapus** untuk konfirmasi atau **Batal** untuk membatalkan

#### Konfirmasi Hapus

Dialog konfirmasi menampilkan informasi:
- Nama produk yang akan dihapus
- Peringatan bahwa tindakan tidak dapat dibatalkan

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
2. Klik tombol **Hapus** (icon trash)
3. Dialog konfirmasi akan muncul
4. Klik **Hapus** untuk konfirmasi atau **Batal** untuk membatalkan

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

### Version 1.0.0 (2025-11-26)
- Initial release admin guide
- Dokumentasi lengkap manajemen produk
- Dokumentasi lengkap manajemen kategori
- Best practices dan troubleshooting

---

**© 2025 Zulfikar Hidayatullah. All Rights Reserved.**

