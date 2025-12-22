# Panduan Administrator Simple Store

## Informasi Dokumen

| Atribut | Detail |
|---------|--------|
| **Nama Dokumen** | Panduan Administrator Simple Store |
| **Developer** | Zulfikar Hidayatullah (+62 857-1583-8733) |
| **Versi** | 1.0.0 |
| **Terakhir Diperbarui** | Desember 2025 |

---

## Pendahuluan

Panduan ini ditujukan untuk administrator aplikasi Simple Store, yaitu: sebuah platform e-commerce sederhana yang menyediakan fitur pengelolaan produk, kategori, pesanan, dan pengaturan toko. Dokumen ini mencakup semua aspek administratif yang diperlukan untuk mengelola toko secara efektif.

---

## Akses Admin Panel

### URL Admin Panel

| Environment | URL |
|-------------|-----|
| **Development** | `http://localhost:8000/admin/dashboard` |
| **Production** | `http://[domain]/admin/dashboard` |

### Kredensial Default

> ⚠️ **Penting**: Kredensial default harus segera diganti setelah instalasi pertama. Lihat `10_CREDENTIALS_ACCESS/` untuk detail lengkap.

### Persyaratan Akses

Untuk mengakses admin panel, pengguna harus:
1. Memiliki akun dengan role **admin**
2. Login dengan kredensial yang valid
3. Menggunakan browser modern (Chrome, Firefox, Safari, Edge terbaru)

---

## Struktur Admin Panel

Admin panel Simple Store memiliki navigasi yang intuitif dengan iOS-like design, yang mencakup:

### Navigation Menu

| Menu | Deskripsi | URL |
|------|-----------|-----|
| **Dashboard** | Overview statistik dan ringkasan toko | `/admin/dashboard` |
| **Produk** | Manajemen produk (CRUD) | `/admin/products` |
| **Kategori** | Manajemen kategori produk | `/admin/categories` |
| **Pesanan** | Manajemen dan monitoring pesanan | `/admin/orders` |
| **Pengaturan** | Konfigurasi toko | `/admin/settings` |

### Mobile Navigation

Untuk pengguna mobile, admin panel menyediakan:
- **Bottom Navigation Bar** - Navigasi cepat ke menu utama
- **Floating Action Button (FAB)** - Quick action untuk tambah produk
- **Pull-to-Refresh** - Refresh data dengan gesture tarik ke bawah
- **Haptic Feedback** - Getaran untuk konfirmasi aksi

---

## Tanggung Jawab Administrator

### 1. Dashboard Monitoring

Administrator bertanggung jawab untuk memonitor:
- **Total Penjualan** - Tracking revenue keseluruhan
- **Pesanan Hari Ini** - Jumlah order yang masuk dalam satu hari
- **Pending Orders** - Pesanan yang menunggu konfirmasi (urgent indicator)
- **Produk Aktif** - Jumlah produk yang tersedia untuk dijual
- **Recent Orders** - Daftar pesanan terbaru dengan status

### 2. Manajemen Produk

Tanggung jawab terkait produk, antara lain:
- Menambah produk baru dengan gambar dan informasi lengkap
- Mengedit detail produk (harga, stok, deskripsi)
- Mengatur status aktif/nonaktif produk
- Menandai produk sebagai featured
- Menghapus produk yang tidak diperlukan (dengan konfirmasi password)

### 3. Manajemen Kategori

Tanggung jawab terkait kategori, antara lain:
- Membuat kategori baru untuk mengelompokkan produk
- Mengedit nama dan detail kategori
- Mengatur urutan tampilan kategori (sort order)
- Mengaktifkan/menonaktifkan kategori

### 4. Manajemen Pesanan

Tanggung jawab terkait pesanan, antara lain:
- Memonitor pesanan baru yang masuk
- Mengkonfirmasi pesanan pending
- Mengupdate status pesanan (Confirmed → Preparing → Ready → Delivered)
- Mengirim notifikasi via WhatsApp ke customer
- Menangani pembatalan pesanan dengan alasan yang jelas

### 5. Konfigurasi Toko

Tanggung jawab terkait pengaturan, antara lain:
- Mengatur informasi toko (nama, alamat, telepon)
- Konfigurasi nomor WhatsApp bisnis
- Mengatur jam operasional per hari
- Konfigurasi delivery (area, biaya, minimum order)
- Upload dan manage logo toko

---

## Fitur Khusus Admin Panel

### Browser Notifications

Admin panel mendukung browser notifications untuk:
- Alert pesanan baru masuk
- Notifikasi status urgent (pending orders)

**Cara Mengaktifkan:**
1. Klik tombol "Aktifkan" pada widget notifikasi di Dashboard
2. Izinkan browser untuk menampilkan notifikasi
3. Notifikasi akan otomatis muncul saat ada pesanan baru

### Quick Actions

Akses cepat ke fitur yang sering digunakan:
- **Tambah Produk** - Langsung ke form tambah produk baru
- **Kelola Kategori** - Akses manajemen kategori
- **Lihat Semua Pesanan** - Jump to orders page

### Real-time Statistics

Dashboard menampilkan statistik real-time:
- Order status breakdown dengan visual chart
- Trend penjualan dengan indicator
- List pesanan terbaru dengan update otomatis

---

## Best Practices untuk Administrator

### Operasional Harian

1. **Cek Dashboard Pagi Hari**
   - Review pending orders dari semalam
   - Konfirmasi pesanan yang sudah valid
   - Cek notifikasi browser sudah aktif

2. **Respon Cepat ke Customer**
   - Konfirmasi pesanan maksimal 30 menit setelah masuk
   - Gunakan fitur WhatsApp untuk komunikasi langsung
   - Update status pesanan secara real-time

3. **Kelola Stok Produk**
   - Monitor stok produk secara berkala
   - Nonaktifkan produk yang stoknya habis
   - Update stok setelah restok

### Manajemen Konten

1. **Gambar Produk**
   - Upload gambar berkualitas baik (minimal 800x800px)
   - Format yang disarankan: JPG atau WebP untuk optimasi
   - Ukuran file maksimal 5MB

2. **Deskripsi Produk**
   - Tulis deskripsi yang jelas dan informatif
   - Sertakan informasi penting (bahan, ukuran, dll)
   - Gunakan bahasa Indonesia yang baik

3. **Kategori yang Terstruktur**
   - Buat kategori yang logis dan mudah dipahami
   - Jangan terlalu banyak kategori
   - Atur sort order untuk kategori populer di atas

---

## Keamanan

### Password & Autentikasi

1. **Password Requirements**
   - Minimal 8 karakter
   - Kombinasi huruf, angka, dan simbol disarankan
   - Ganti password secara berkala (setiap 3 bulan)

2. **Two-Factor Authentication (2FA)**
   - Aktifkan 2FA untuk keamanan tambahan
   - Simpan recovery codes di tempat aman
   - Gunakan authenticator app (Google Authenticator, Authy)

3. **Konfirmasi Password untuk Aksi Sensitif**
   - Hapus produk memerlukan konfirmasi password
   - Perubahan pengaturan kritikal memerlukan verifikasi

### Session Management

- Session timeout: Auto-logout setelah periode tidak aktif
- Logout manual: Selalu logout setelah selesai bekerja di device bersama
- Single session: Gunakan satu device untuk akses admin

### Privasi Data

1. **Data Customer**
   - Data customer hanya digunakan untuk keperluan order
   - Jangan share data customer ke pihak ketiga
   - Hapus data customer sesuai retention policy

2. **Log Access**
   - Semua aktivitas admin tercatat di log
   - Review log secara berkala untuk audit

---

## Troubleshooting

### Masalah Umum

| Problem | Solusi |
|---------|--------|
| Dashboard tidak loading | Refresh halaman atau clear cache browser |
| Notifikasi tidak muncul | Cek izin notifikasi browser, pastikan sudah "Allow" |
| Upload gambar gagal | Pastikan ukuran file < 5MB dan format JPG/PNG/WebP |
| Login gagal | Reset password atau hubungi developer |
| Data tidak update | Pull-to-refresh atau hard refresh (Ctrl+Shift+R) |

### Error Messages

| Error | Penyebab | Solusi |
|-------|----------|--------|
| "Session expired" | Session timeout | Login ulang |
| "Permission denied" | Akses tidak diizinkan | Hubungi super admin |
| "File too large" | Ukuran file melebihi limit | Kompres file sebelum upload |
| "Invalid credentials" | Password salah | Cek capslock, reset password jika lupa |

---

## Kontak Support

Jika mengalami kendala atau membutuhkan bantuan teknis:

| Kontak | Detail |
|--------|--------|
| **Developer** | Zulfikar Hidayatullah |
| **WhatsApp** | +62 857-1583-8733 |
| **Email** | [Email Developer] |

---

## Dokumen Terkait

- `02_User_Management.md` - Detail manajemen pengguna
- `03_Content_Management.md` - Panduan manajemen konten (produk & kategori)
- `04_Settings_Configuration.md` - Konfigurasi pengaturan toko
- `05_Order_Management.md` - Panduan lengkap manajemen pesanan
