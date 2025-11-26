<!-- 5a80028a-31b5-43ab-817c-c5d1f9b455f3 0cdec666-1717-4daa-91a8-8d329232d025 -->
# Sprint 2: Implementasi Checkout & WhatsApp Integration

## Implementation Steps

### 1. CUST-007: Checkout Form Implementation

**Backend:**

- Buat `CheckoutController` dengan method `show()` untuk menampilkan form dan `store()` untuk proses checkout
- Buat `CheckoutRequest` untuk validasi form (name, phone, address, notes)
- Buat migration untuk tabel `orders` dan `order_items`
- Buat Model `Order` dan `OrderItem` dengan relationships
- Buat `OrderService` untuk business logic pembuatan order
- Tambahkan routes untuk checkout di [`routes/web.php`](routes/web.php)

**Frontend:**

- Buat halaman [`resources/js/pages/Checkout.vue`](resources/js/pages/Checkout.vue) dengan form lengkap
- Implementasi form validation real-time
- Tampilkan order summary dari cart
- Gunakan Inertia Form component untuk form handling
- Update button "Lanjut ke Checkout" di [`resources/js/pages/Cart.vue`](resources/js/pages/Cart.vue)

**Testing:**

- Buat feature test untuk checkout flow
- Test validasi form (required fields, format phone)
- Test cart kosong tidak bisa checkout

### 2. CUST-008: WhatsApp Integration

**Backend:**

- Extend `OrderService` dengan method untuk generate WhatsApp message
- Implementasi method `createOrder()` yang menyimpan order ke database
- Method `generateWhatsAppMessage()` untuk format pesan order
- Method `generateWhatsAppUrl()` untuk generate URL WhatsApp dengan pre-filled message
- Update `CheckoutController::store()` untuk return WhatsApp URL
- Tambahkan config untuk WhatsApp business number di `.env`

**Frontend:**

- Update [`resources/js/pages/Checkout.vue`](resources/js/pages/Checkout.vue) untuk handle submit
- Redirect ke WhatsApp setelah order tersimpan
- Tampilkan loading state saat proses order
- Handle error scenarios

**Testing:**

- Test pembuatan order dengan cart items
- Test format WhatsApp message
- Test clear cart setelah order
- Test WhatsApp URL generation

### 3. CUST-009: Order Confirmation Page

**Backend:**

- Tambahkan method `success()` di `CheckoutController` untuk halaman konfirmasi
- Method menerima order_id dan menampilkan detail order
- Implementasi policy/authorization untuk memastikan order milik session yang benar

**Frontend:**

- Buat halaman [`resources/js/pages/OrderSuccess.vue`](resources/js/pages/OrderSuccess.vue)
- Tampilkan order number dan detail pesanan
- Tampilkan customer info dan items yang dipesan
- Tambahkan tombol untuk kembali ke home atau WhatsApp
- Animasi success state

**Testing:**

- Test halaman success menampilkan data order dengan benar
- Test cart sudah kosong setelah order
- Test tidak bisa akses order orang lain

### 4. Documentation Update

Buat dokumentasi lengkap sesuai writing-style Indonesian formal:

- [`docs/07-development/sprint-planning/sprint-2-cust-007.md`](docs/07-development/sprint-planning/sprint-2-cust-007.md) - Dokumentasi lengkap CUST-007 dengan struktur: Overview, User Story, Acceptance Criteria, Technical Implementation, Testing, Results
- [`docs/07-development/sprint-planning/sprint-2-cust-008.md`](docs/07-development/sprint-planning/sprint-2-cust-008.md) - Dokumentasi CUST-008 mencakup WhatsApp API integration dan message formatting
- [`docs/07-development/sprint-planning/sprint-2-cust-009.md`](docs/07-development/sprint-planning/sprint-2-cust-009.md) - Dokumentasi CUST-009 untuk order confirmation flow

Update file utama:

- Update [`docs/README.md`](docs/README.md) - Update Sprint 2 Progress table dengan status completed untuk CUST-007, CUST-008, CUST-009
- Update [`scrum.md`](scrum.md) - Mark Sprint 2 stories as completed

### 5. Commit Message

Buat commit message yang mengikuti pattern existing:

- Format: `feat(checkout): implement checkout and whatsapp integration features`
- Include detail untuk ketiga user stories
- Reference CUST-007, CUST-008, CUST-009

## Key Files to Create/Modify

**New Files:**

- `app/Http/Controllers/CheckoutController.php`
- `app/Http/Requests/CheckoutRequest.php`
- `app/Services/OrderService.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `database/migrations/[timestamp]_create_orders_table.php`
- `database/migrations/[timestamp]_create_order_items_table.php`
- `resources/js/pages/Checkout.vue`
- `resources/js/pages/OrderSuccess.vue`
- `tests/Feature/CheckoutTest.php`
- `docs/07-development/sprint-planning/sprint-2-cust-007.md`
- `docs/07-development/sprint-planning/sprint-2-cust-008.md`
- `docs/07-development/sprint-planning/sprint-2-cust-009.md`

**Modified Files:**

- `routes/web.php` - Add checkout routes
- `resources/js/pages/Cart.vue` - Update checkout button
- `docs/README.md` - Update progress
- `scrum.md` - Mark as completed

## Expected Outcome

Setelah implementasi selesai, customer akan dapat:

1. Mengisi form checkout dengan data lengkap (name, phone, address, notes)
2. Melihat ringkasan pesanan sebelum checkout
3. Submit order dan otomatis diarahkan ke WhatsApp dengan pesan terformat
4. Melihat halaman konfirmasi order dengan detail lengkap
5. Cart otomatis kosong setelah order berhasil

### To-dos

- [ ] Implement checkout backend (Controller, Request, Service, Models, Migrations)
- [ ] Create Checkout.vue page with form and validation
- [ ] Implement WhatsApp message generation and URL integration
- [ ] Create OrderSuccess.vue page and success flow
- [ ] Write feature tests for checkout flow
- [ ] Create sprint documentation for CUST-007, CUST-008, CUST-009
- [ ] Create commit message following project writing-style