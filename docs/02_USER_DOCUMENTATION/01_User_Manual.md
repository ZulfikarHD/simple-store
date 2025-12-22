# Manual Pengguna Simple Store

## Pendahuluan

Panduan ini bertujuan untuk membantu pengguna dalam menggunakan aplikasi Simple Store, yaitu: sebuah platform e-commerce sederhana yang dirancang dengan prinsip iOS-like design untuk memberikan pengalaman berbelanja yang optimal di perangkat mobile maupun desktop.

**Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)

---

## Cara Mengakses Aplikasi

### URL Akses
- **Storefront (Pelanggan)**: `http://[domain]/` - Halaman utama katalog produk
- **Admin Dashboard**: `http://[domain]/admin/dashboard` - Panel administrasi

### Persyaratan Browser
Aplikasi mendukung browser modern, antara lain:
- Google Chrome (versi terbaru)
- Mozilla Firefox (versi terbaru)
- Safari (versi terbaru)
- Microsoft Edge (versi terbaru)

### Akses Mobile
Aplikasi telah dioptimasi untuk penggunaan mobile dengan fitur:
- **Pull-to-Refresh**: Tarik layar ke bawah untuk memuat ulang data
- **Bottom Navigation**: Navigasi mudah di bagian bawah layar
- **Haptic Feedback**: Getaran halus saat interaksi (pada perangkat yang mendukung)

---

## Fitur Utama

### Untuk Pelanggan (Customer)
| Fitur | Deskripsi |
|-------|-----------|
| Browsing Produk | Melihat daftar produk dengan filter kategori dan pencarian |
| Detail Produk | Halaman lengkap dengan gambar, harga, dan deskripsi |
| Keranjang Belanja | Menambah, mengubah, dan menghapus item |
| Checkout | Melengkapi data pengiriman dan membuat pesanan |
| Konfirmasi WhatsApp | Mengirim pesanan ke toko via WhatsApp |
| Akun Pengguna | Registrasi, login, dan manajemen profil |
| Riwayat Pesanan | Melihat pesanan yang pernah dibuat (untuk user terdaftar) |

### Untuk Admin
| Fitur | Deskripsi |
|-------|-----------|
| Dashboard | Overview statistik penjualan dan pesanan |
| Manajemen Produk | Membuat, mengedit, dan menghapus produk |
| Manajemen Kategori | Mengatur kategori produk |
| Manajemen Pesanan | Melihat dan memperbarui status pesanan |
| Pengaturan Toko | Konfigurasi nama toko, logo, dan WhatsApp |

---

## Panduan Step-by-Step

### 1. Registrasi Akun

#### Registrasi Manual

1. Akses halaman utama aplikasi
2. Klik tombol **"Daftar"** di pojok kanan atas (desktop) atau melalui menu hamburger (mobile)
3. Isi form registrasi dengan data berikut:
   - **Nama Lengkap**: Nama asli Anda
   - **Email**: Alamat email yang valid
   - **Password**: Minimal 8 karakter
   - **Konfirmasi Password**: Ulangi password yang sama
4. Klik tombol **"Daftar"**
5. Anda akan otomatis login dan diarahkan ke halaman utama

#### Registrasi dengan Google (Lebih Cepat)

Cara tercepat untuk membuat akun adalah menggunakan Google:

1. Akses halaman registrasi atau login
2. Klik tombol **"Daftar dengan Google"** atau **"Masuk dengan Google"**
3. Pilih akun Google yang ingin digunakan
4. Berikan izin akses untuk Simple Store
5. Akun Anda otomatis dibuat dan langsung login

**Catatan:** Registrasi via Google tidak memerlukan password karena autentikasi menggunakan Google account Anda.

### 2. Login

#### Login dengan Email & Password

1. Klik tombol **"Masuk"** di pojok kanan atas
2. Masukkan **Email** dan **Password** yang telah terdaftar
3. (Opsional) Centang **"Ingat saya"** untuk tetap login
4. Klik tombol **"Masuk"**
5. Jika berhasil, Anda akan diarahkan ke halaman sebelumnya atau halaman akun

#### Login dengan Google

Anda juga dapat login menggunakan akun Google untuk proses yang lebih cepat:

1. Klik tombol **"Masuk"** di pojok kanan atas
2. Klik tombol **"Masuk dengan Google"** (tombol putih dengan logo Google)
3. Pilih akun Google yang ingin digunakan
4. Berikan izin akses untuk Simple Store
5. Anda akan otomatis login dan diarahkan ke halaman utama

**Keuntungan Login dengan Google:**
- ✅ Tidak perlu mengingat password
- ✅ Proses login lebih cepat (one-click)
- ✅ Email otomatis terverifikasi
- ✅ Avatar dari Google otomatis digunakan
- ✅ Akun baru otomatis dibuat jika belum terdaftar

> **💡 Tips**: 
> - Anda dapat mengaktifkan **Two-Factor Authentication (2FA)** untuk keamanan tambahan melalui menu **Akun → Keamanan**.
> - Jika Anda login dengan Google dan sudah punya akun dengan email yang sama, akun akan otomatis di-link.

### 3. Browsing Produk

1. Di halaman utama, Anda akan melihat katalog produk dalam format grid
2. Gunakan fitur berikut untuk menemukan produk:
   - **Filter Kategori**: Klik chip kategori untuk memfilter produk
   - **Pencarian**: Klik ikon 🔍 (mobile) atau gunakan search bar (desktop) untuk mencari produk
3. Klik kartu produk untuk melihat detail lengkap

### 4. Melihat Detail Produk

1. Pada halaman detail produk, Anda dapat melihat:
   - **Gambar Produk**: Dengan efek parallax saat scroll
   - **Nama dan Kategori**: Informasi produk
   - **Harga**: Dalam format Rupiah
   - **Status Stok**: Tersedia, Stok Terbatas, atau Habis
   - **Deskripsi**: Penjelasan lengkap produk
   - **Produk Terkait**: Produk serupa yang mungkin Anda suka
2. Atur jumlah yang diinginkan dengan tombol **-** dan **+**
3. Klik **"Tambah ke Keranjang"**

### 5. Mengelola Keranjang Belanja

1. Klik ikon keranjang 🛒 di header atau melalui bottom navigation
2. Di halaman keranjang, Anda dapat:
   - **Mengubah Jumlah**: Gunakan tombol **-** dan **+** pada setiap item
   - **Menghapus Item**: Geser item ke kiri (swipe-to-delete) atau klik ikon hapus
3. Lihat ringkasan total di bagian bawah
4. Klik **"Lanjut ke Checkout"** untuk melanjutkan

### 6. Proses Checkout

1. Pada halaman checkout, lengkapi **Data Penerima**:
   - **Nama Lengkap** (wajib): Nama penerima pesanan
   - **Nomor Telepon/WhatsApp** (wajib): Untuk konfirmasi pesanan
   - **Alamat Lengkap** (opsional): Alamat pengiriman
   - **Catatan** (opsional): Informasi tambahan
2. Review **Ringkasan Pesanan** di sidebar
3. Klik tombol **"Pesan via WhatsApp"**
4. Anda akan diarahkan ke halaman sukses

### 7. Konfirmasi Pesanan via WhatsApp

1. Setelah pesanan dibuat, halaman sukses akan menampilkan:
   - **Nomor Pesanan**: Simpan untuk referensi
   - **Detail Pesanan**: Item yang dipesan
   - **Data Penerima**: Informasi pengiriman
2. **PENTING**: Klik tombol hijau **"Konfirmasi via WhatsApp"** untuk mengirim pesanan ke toko
3. WhatsApp akan terbuka dengan pesan pre-filled berisi detail pesanan
4. Kirim pesan tersebut untuk mengkonfirmasi pesanan Anda

> ⚠️ **Perhatian**: Pesanan belum dikonfirmasi sampai Anda mengirim pesan WhatsApp ke toko!

### 8. Melihat Riwayat Pesanan (User Terdaftar)

1. Klik tab **"Akun"** di bottom navigation atau menu
2. Pilih **"Riwayat Pesanan"**
3. Anda dapat melihat daftar pesanan dengan status:
   - **Pending**: Menunggu konfirmasi
   - **Confirmed**: Pesanan dikonfirmasi
   - **Preparing**: Sedang disiapkan
   - **Ready**: Siap diambil/dikirim
   - **Delivered**: Sudah diterima
   - **Cancelled**: Dibatalkan

---

## Tips dan Trik

### Untuk Pengalaman Terbaik
1. **Gunakan Browser Terbaru**: Pastikan browser Anda sudah diperbarui untuk performa optimal
2. **Aktifkan Notifikasi**: Admin dapat mengaktifkan notifikasi browser untuk alert pesanan baru
3. **Pull-to-Refresh**: Tarik layar ke bawah untuk memuat data terbaru
4. **Guest Checkout**: Anda dapat berbelanja tanpa registrasi, namun riwayat pesanan tidak tersimpan

### Shortcuts Mobile
- **Bottom Navigation**: Gunakan bar navigasi bawah untuk akses cepat ke Beranda, Keranjang, dan Akun
- **Swipe Gestures**: Geser item keranjang ke kiri untuk menghapus dengan cepat
- **Search Expand**: Tap ikon pencarian untuk membuka search bar di mobile

### Keamanan Akun
1. Gunakan password yang kuat (minimal 8 karakter, kombinasi huruf dan angka)
2. Aktifkan **Two-Factor Authentication** di menu Keamanan
3. Jangan bagikan kredensial akun Anda

---

## FAQ (Pertanyaan yang Sering Diajukan)

### Umum

**Q: Apakah saya harus registrasi untuk berbelanja?**
> A: Tidak. Anda dapat berbelanja sebagai guest tanpa registrasi. Keranjang akan tersimpan berdasarkan session browser Anda.

**Q: Bagaimana cara menghubungi toko?**
> A: Gunakan tombol "Konfirmasi via WhatsApp" setelah membuat pesanan, atau hubungi nomor WhatsApp toko yang tertera di footer.

**Q: Apakah data saya aman?**
> A: Ya. Aplikasi menggunakan enkripsi dan praktik keamanan Laravel terbaik untuk melindungi data Anda.

### Pesanan

**Q: Pesanan saya sudah dibuat tapi belum dikonfirmasi?**
> A: Pastikan Anda sudah mengirim pesan WhatsApp ke toko. Pesanan baru dikonfirmasi setelah toko menerima pesan WhatsApp.

**Q: Bagaimana cara membatalkan pesanan?**
> A: Hubungi toko via WhatsApp untuk permintaan pembatalan. Pembatalan hanya dapat dilakukan sebelum status "Preparing".

**Q: Berapa lama pesanan diproses?**
> A: Waktu proses tergantung kebijakan toko. Toko akan menginformasikan estimasi waktu via WhatsApp.

### Teknis

**Q: Halaman tidak bisa dimuat / error?**
> A: Coba refresh halaman atau clear cache browser. Jika masih bermasalah, hubungi admin.

**Q: Notifikasi tidak muncul?**
> A: Pastikan Anda sudah mengizinkan notifikasi browser saat diminta. Periksa juga pengaturan notifikasi browser Anda.

**Q: Keranjang saya hilang?**
> A: Keranjang guest tersimpan di session browser. Jika Anda clear cookies atau menggunakan browser berbeda, keranjang akan reset.

---

## Kontak Bantuan

Jika Anda mengalami kendala atau memiliki pertanyaan, silakan hubungi:

- **Developer**: Zulfikar Hidayatullah
- **WhatsApp**: +62 857-1583-8733
- **Fitur dalam Aplikasi**: Gunakan tombol WhatsApp di halaman sukses pesanan
