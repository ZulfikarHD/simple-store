# Panduan Fitur Simple Store

## Daftar Fitur Lengkap

Dokumentasi ini menjelaskan secara detail setiap fitur yang tersedia dalam aplikasi Simple Store, mencakup fitur untuk pelanggan (customer) dan administrator (admin).

---

## Bagian A: Fitur Customer (Pelanggan)

### 1. Katalog Produk (Home)

**Deskripsi**: Halaman utama yang menampilkan seluruh produk aktif dalam format grid responsif dengan iOS-like animations.

**Fitur yang Tersedia**:
- **Grid View**: Tampilan produk dalam grid 2 kolom (mobile) hingga 4 kolom (desktop)
- **Filter Kategori**: Horizontal scrollable chips untuk memfilter produk berdasarkan kategori
- **Pencarian**: Search bar dengan debounce untuk mencari produk berdasarkan nama
- **Pull-to-Refresh**: Tarik layar ke bawah untuk reload data (mobile)
- **Staggered Animation**: Animasi masuk bertahap untuk setiap kartu produk
- **Active Orders Section**: Quick access untuk melihat pesanan aktif

**Cara Menggunakan**:
1. Buka halaman utama aplikasi
2. Scroll untuk melihat produk
3. Tap chip kategori untuk memfilter
4. Tap ikon search (🔍) untuk mencari produk
5. Tap kartu produk untuk melihat detail

**Screenshot**: `screenshots/home/`

---

### 2. Detail Produk

**Deskripsi**: Halaman informasi lengkap produk dengan parallax image effect dan quantity selector.

**Fitur yang Tersedia**:
- **Parallax Image**: Efek gambar yang bergerak saat scroll
- **Breadcrumb Navigation**: Navigasi hierarkis (Beranda → Kategori → Produk)
- **Stock Status Badge**: Indikator status stok (Tersedia, Stok Terbatas, Habis)
- **Quantity Selector**: Tombol +/- dengan haptic feedback
- **Add to Cart Button**: Tombol dengan loading state dan success animation
- **Related Products**: Carousel produk terkait dari kategori yang sama
- **Sticky Footer (Mobile)**: Panel harga dan add to cart yang selalu terlihat

**Cara Menggunakan**:
1. Klik produk dari katalog
2. Scroll untuk melihat deskripsi lengkap
3. Atur jumlah dengan tombol **-** dan **+**
4. Klik **"Tambah ke Keranjang"**
5. Lihat produk terkait di bagian bawah

**Screenshot**: `screenshots/product-detail/`

---

### 3. Keranjang Belanja

**Deskripsi**: Halaman pengelolaan item yang akan dibeli dengan iOS-like interactions.

**Fitur yang Tersedia**:
- **Cart Item List**: Daftar item dengan gambar, nama, harga, dan quantity
- **Quantity Control**: Ubah jumlah item dengan tombol +/-
- **Swipe-to-Delete**: Geser item ke kiri untuk menghapus (mobile)
- **Animated Totals**: Animasi pada perubahan total harga
- **Order Summary**: Ringkasan subtotal dan estimasi total
- **Sticky Checkout Footer**: Panel checkout yang selalu terlihat (mobile)
- **Empty State**: Tampilan khusus jika keranjang kosong

**Cara Menggunakan**:
1. Klik ikon keranjang di header atau bottom navigation
2. Review item di keranjang
3. Ubah jumlah atau hapus item sesuai kebutuhan
4. Klik **"Lanjut ke Checkout"**

**Screenshot**: `screenshots/cart/`

---

### 4. Checkout

**Deskripsi**: Halaman form data pengiriman untuk menyelesaikan pesanan dengan validasi nama yang ketat.

**Fitur yang Tersedia**:
- **Form Data Penerima**: Input nama depan, nama belakang, dan nomor telepon
- **Validasi Nama Ketat**: Nama tidak boleh menggunakan gelar (Pak, Bu, Mr, Mrs, dll) dan simbol
- **Form Alamat Pengiriman**: Input alamat dan catatan tambahan
- **iOS-like Form Inputs**: Input dengan focus animation dan validation
- **Error Shake Animation**: Animasi getaran pada field dengan error
- **Order Summary Sidebar**: Ringkasan pesanan di samping form
- **Items Preview**: Daftar item yang akan dipesan
- **Submit via WhatsApp**: Button untuk mengirim pesanan

**Cara Menggunakan**:
1. Isi **Nama Depan** (wajib, nama asli tanpa gelar)
2. Isi **Nama Belakang** (wajib, nama asli tanpa gelar)
3. Isi **Nomor Telepon/WhatsApp** (wajib)
4. Isi **Alamat Lengkap** (opsional)
5. Isi **Catatan** jika diperlukan (opsional)
6. Review ringkasan pesanan
7. Klik **"Pesan via WhatsApp"**

**Validasi Nama**:
| Field | Validasi |
|-------|----------|
| Nama Depan | Wajib, min 2 karakter, hanya huruf dan spasi, tidak boleh gelar |
| Nama Belakang | Wajib, min 2 karakter, hanya huruf dan spasi, tidak boleh gelar |

**Gelar yang Tidak Diperbolehkan**:
- English: Sir, Mr, Mrs, Ms, Miss, Dr, Prof, dll
- Indonesia: Pak, Bu, Bapak, Ibu, Mas, Mbak, Kak, Bang, Tuan, Nyonya, dll

**Screenshot**: `screenshots/checkout/`

---

### 5. Halaman Sukses Pesanan

**Deskripsi**: Halaman konfirmasi setelah pesanan berhasil dibuat dengan celebration animation.

**Fitur yang Tersedia**:
- **Success Celebration**: Animasi sukses dengan haptic feedback
- **Order Number Display**: Nomor pesanan dengan tombol copy
- **WhatsApp Alert**: Peringatan penting untuk konfirmasi via WhatsApp
- **Customer Info Summary**: Ringkasan data penerima
- **Order Details**: Detail item dan total pesanan
- **WhatsApp CTA Button**: Tombol utama untuk konfirmasi pesanan
- **Sticky WhatsApp Footer (Mobile)**: CTA yang selalu terlihat

**Cara Menggunakan**:
1. Catat atau copy **Nomor Pesanan**
2. Review detail pesanan
3. **WAJIB**: Klik tombol hijau **"Konfirmasi via WhatsApp"**
4. Kirim pesan WhatsApp yang sudah disiapkan
5. Kembali ke beranda untuk belanja lagi

**Screenshot**: `screenshots/order-success/`

---

### 6. Halaman Akun

**Deskripsi**: Halaman profil dan menu user untuk pengguna yang sudah login, dengan dukungan multiple authentication methods termasuk Google OAuth.

**Fitur yang Tersedia**:
- **Profile Card**: Menampilkan nama, email, avatar, dan tanggal bergabung
- **Google Avatar**: Avatar dari Google account jika login via Google
- **Email Verification Badge**: Status verifikasi email (otomatis verified untuk Google login)
- **Menu Navigation**: Akses ke riwayat pesanan, pengaturan, dan keamanan
- **Logout Button**: Tombol keluar dari akun
- **Guest Prompt**: Tampilan login/register untuk guest user dengan opsi Google login

**Menu yang Tersedia**:
| Menu | Deskripsi |
|------|-----------|
| Riwayat Pesanan | Melihat daftar pesanan yang pernah dibuat |
| Pengaturan Akun | Mengubah profil dan password |
| Keamanan | Mengaktifkan Two-Factor Authentication |

**Cara Menggunakan**:
1. Klik tab **"Akun"** di bottom navigation
2. Jika belum login, pilih **"Masuk"**, **"Daftar"**, atau **"Masuk dengan Google"**
3. Jika sudah login, pilih menu yang diinginkan
4. Klik **"Keluar"** untuk logout

**Screenshot**: `screenshots/account/`

---

### 6.1. Google OAuth Authentication

**Deskripsi**: Fitur login dan registrasi menggunakan akun Google untuk proses authentication yang lebih cepat dan aman.

**Keuntungan**:
- ✅ **One-Click Login**: Tidak perlu mengingat password tambahan
- ✅ **Auto-Registration**: Akun otomatis dibuat untuk user baru
- ✅ **Email Verified**: Email otomatis terverifikasi
- ✅ **Profile Sync**: Avatar dan nama dari Google otomatis digunakan
- ✅ **Account Linking**: Link Google ke akun existing berdasarkan email

**Cara Menggunakan - Login**:
1. Buka halaman **Login**
2. Klik tombol **"Masuk dengan Google"** (tombol putih dengan logo Google)
3. Pilih akun Google yang ingin digunakan
4. Berikan izin akses untuk Simple Store
5. Anda akan otomatis login dan diarahkan ke halaman utama

**Cara Menggunakan - Registrasi**:
1. Buka halaman **Daftar**
2. Klik tombol **"Daftar dengan Google"**
3. Pilih akun Google yang ingin digunakan
4. Berikan izin akses untuk Simple Store
5. Akun baru otomatis dibuat dan Anda langsung login

**Skenario Account Linking**:
- Jika Anda sudah punya akun dengan email `user@gmail.com`
- Kemudian login dengan Google menggunakan email yang sama
- Sistem akan otomatis link Google account ke akun existing Anda
- Anda bisa login dengan Google atau email-password di masa depan

**Catatan Keamanan**:
- Google OAuth menggunakan OAuth 2.0 standard yang aman
- Simple Store tidak menyimpan password Google Anda
- Anda bisa revoke akses kapan saja di Google Account Settings

**Screenshot**: `screenshots/auth/google-login/`

---

### 7. Riwayat Pesanan

**Deskripsi**: Halaman daftar pesanan yang pernah dibuat oleh user terdaftar.

**Fitur yang Tersedia**:
- **Order List**: Daftar pesanan dengan nomor, status, dan total
- **Status Badge**: Indikator status dengan warna berbeda
- **Order Detail View**: Halaman detail pesanan lengkap
- **Empty State**: Tampilan jika belum ada pesanan

**Status Pesanan**:
| Status | Warna | Deskripsi |
|--------|-------|-----------|
| Pending | Kuning | Menunggu konfirmasi toko |
| Confirmed | Biru | Pesanan dikonfirmasi |
| Preparing | Ungu | Sedang disiapkan |
| Ready | Hijau | Siap diambil/dikirim |
| Delivered | Hijau Tua | Sudah diterima |
| Cancelled | Merah | Dibatalkan |

**Screenshot**: `screenshots/order-history/`

---

## Bagian B: Fitur Admin (Administrator)

### 1. Admin Dashboard

**Deskripsi**: Halaman overview statistik toko dengan metrics utama dan real-time updates.

**Fitur yang Tersedia**:
- **Total Sales Card**: Menampilkan total penjualan keseluruhan
- **Today Orders**: Jumlah pesanan hari ini
- **Pending Orders**: Pesanan yang menunggu konfirmasi dengan pulse indicator
- **Active Products**: Jumlah produk aktif
- **Recent Orders List**: 5 pesanan terbaru dengan quick actions
- **Status Breakdown**: Distribusi status pesanan
- **Browser Notifications**: Alert untuk pesanan baru
- **Quick Actions**: Shortcut ke menu utama

**Cara Menggunakan**:
1. Login sebagai admin
2. Akses `/admin/dashboard`
3. Aktifkan notifikasi browser untuk alert pesanan baru
4. Klik pesanan untuk melihat detail
5. Gunakan Quick Actions untuk navigasi cepat

**Screenshot**: `screenshots/admin/dashboard/`

---

### 2. Manajemen Produk

**Deskripsi**: CRUD (Create, Read, Update, Delete) untuk pengelolaan produk toko.

**Fitur yang Tersedia**:
- **Product List**: Tabel produk dengan search dan pagination
- **Create Product**: Form tambah produk baru
- **Edit Product**: Form ubah data produk
- **Delete Product**: Hapus produk dengan konfirmasi
- **Image Upload**: Upload gambar produk
- **Stock Management**: Pengaturan stok produk
- **Active Toggle**: Mengaktifkan/menonaktifkan produk
- **Featured Toggle**: Menandai produk unggulan

**Form Fields**:
| Field | Tipe | Keterangan |
|-------|------|------------|
| Nama Produk | Text | Wajib, nama produk |
| Kategori | Select | Wajib, pilih dari kategori tersedia |
| Harga | Number | Wajib, dalam Rupiah |
| Stok | Number | Wajib, jumlah stok tersedia |
| Deskripsi | Textarea | Opsional, penjelasan produk |
| Gambar | File | Opsional, format JPG/PNG |
| Aktif | Toggle | Status produk ditampilkan |
| Unggulan | Toggle | Status produk featured |

**Cara Menggunakan**:
1. Akses **Admin → Produk**
2. Klik **"Tambah Produk"** untuk membuat baru
3. Isi form dengan data produk
4. Upload gambar (opsional)
5. Klik **"Simpan"**

**Screenshot**: `screenshots/admin/products/`

---

### 3. Manajemen Kategori

**Deskripsi**: Pengelolaan kategori produk untuk organisasi katalog.

**Fitur yang Tersedia**:
- **Category List**: Daftar kategori dengan jumlah produk
- **Create Category**: Form tambah kategori baru
- **Edit Category**: Form ubah data kategori
- **Delete Category**: Hapus kategori (jika tidak ada produk)
- **Image Upload**: Upload gambar kategori
- **Sort Order**: Mengatur urutan tampilan
- **Active Toggle**: Mengaktifkan/menonaktifkan kategori

**Form Fields**:
| Field | Tipe | Keterangan |
|-------|------|------------|
| Nama Kategori | Text | Wajib, nama kategori |
| Slug | Text | Auto-generated dari nama |
| Deskripsi | Textarea | Opsional, penjelasan kategori |
| Gambar | File | Opsional, format JPG/PNG |
| Urutan | Number | Urutan tampilan di katalog |
| Aktif | Toggle | Status kategori ditampilkan |

**Cara Menggunakan**:
1. Akses **Admin → Kategori**
2. Klik **"Tambah Kategori"** untuk membuat baru
3. Isi nama dan deskripsi
4. Atur urutan tampilan
5. Klik **"Simpan"**

**Screenshot**: `screenshots/admin/categories/`

---

### 4. Manajemen Pesanan

**Deskripsi**: Melihat dan mengelola status pesanan dari pelanggan.

**Fitur yang Tersedia**:
- **Order List**: Daftar pesanan dengan filter dan search
- **Status Filter**: Filter berdasarkan status pesanan
- **Order Detail**: Halaman detail pesanan lengkap
- **Update Status**: Mengubah status pesanan
- **Quick Status Update**: Update status dari list view
- **Customer Info**: Informasi pelanggan dan alamat
- **Order Items**: Detail item yang dipesan

**Status Workflow**:
```
Pending → Confirmed → Preparing → Ready → Delivered
                                      ↘ Cancelled
```

**Cara Menggunakan**:
1. Akses **Admin → Pesanan**
2. Lihat daftar pesanan
3. Filter berdasarkan status jika diperlukan
4. Klik pesanan untuk detail
5. Update status sesuai progress
6. Hubungi pelanggan via WhatsApp jika perlu

**Screenshot**: `screenshots/admin/orders/`

---

### 5. Pengaturan Toko

**Deskripsi**: Konfigurasi informasi dan tampilan toko.

**Settings yang Tersedia**:
| Setting | Tipe | Deskripsi |
|---------|------|-----------|
| Nama Toko | Text | Nama yang ditampilkan di header |
| Tagline | Text | Slogan toko di footer |
| Logo | Image | Logo toko (tampil di header) |
| Nomor WhatsApp | Text | Nomor untuk konfirmasi pesanan |
| Alamat Toko | Textarea | Alamat fisik toko |
| Jam Operasional | Text | Jam buka toko |

**Cara Menggunakan**:
1. Akses **Admin → Pengaturan**
2. Edit field yang diinginkan
3. Upload logo baru jika perlu
4. Klik **"Simpan Pengaturan"**

**Screenshot**: `screenshots/admin/settings/`

---

## Fitur Tambahan (Cross-cutting)

### Bottom Navigation (Mobile)
Navigasi utama di bagian bawah layar untuk akses cepat:
- **Beranda**: Kembali ke katalog produk
- **Keranjang**: Akses keranjang belanja dengan badge counter
- **Akun**: Menu akun dan pengaturan

### iOS-like Interactions
- **Spring Animations**: Animasi bouncy seperti iOS
- **Press Feedback**: Efek scale-down saat tombol ditekan
- **Haptic Feedback**: Getaran halus pada interaksi (device supported)
- **Glass Effect**: Efek frosted glass pada header dan footer

### Dark Mode Support
Aplikasi mendukung dark mode dengan tema yang menyesuaikan preferensi sistem.

### Pull-to-Refresh
Tarik layar ke bawah untuk memuat ulang data di halaman utama dan keranjang.

### Browser Notifications (Admin)
Admin dapat mengaktifkan notifikasi browser untuk menerima alert saat ada pesanan baru.
