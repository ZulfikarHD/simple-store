<!-- e92da497-98fd-4da4-aef3-ebc282defec1 0ab082e5-9f35-48f4-83fa-6807b6c15f68 -->
# Plan: Implementasi CUST-001 - Product Catalog Display

## Overview

Mengimplementasikan user story CUST-001 dari scrum.md untuk menampilkan katalog produk pada halaman home dengan grid layout responsive, mencakup gambar, nama, harga, dan deskripsi produk.

## Acceptance Criteria

- Home page menampilkan semua produk aktif dengan gambar
- Produk menampilkan name, price, description
- Responsive grid layout (menggunakan Tailwind CSS)

## Backend Changes

### 1. Create Product Controller

File: `app/Http/Controllers/ProductController.php`

- Membuat controller dengan method `index()` untuk menampilkan daftar produk
- Query produk yang aktif dengan eager loading category
- Gunakan Eloquent scope `active()` dan `with('category')`
- Return Inertia render dengan data products

### 2. Create Product Resource

File: `app/Http/Resources/ProductResource.php`

- Membuat API Resource untuk format data produk yang konsisten
- Include: id, name, slug, description, price, image, category, is_available
- Format harga dan URL gambar dengan benar
- Gunakan `php artisan make:resource ProductResource`

### 3. Update Routes

File: `routes/web.php`

- Ubah route `/` dari Welcome page ke ProductController@index
- Update route name tetap `home` untuk konsistensi dengan existing code
- Route harus accessible tanpa auth (public)

## Frontend Changes

### 4. Create/Update Home Page Component

File: `resources/js/pages/Home.vue` (new) atau update `Welcome.vue`

- Buat Vue component untuk halaman product catalog
- Props: `products` (array dari ProductResource)
- Layout: Grid responsive menggunakan Tailwind
- Mobile: 1-2 kolom
- Tablet: 2-3 kolom  
- Desktop: 3-4 kolom
- Gunakan existing `ProductCard.vue` component untuk display setiap produk
- Include empty state jika tidak ada produk
- Setup Head title "Katalog Produk"

### 5. Verify ProductCard Component

File: `resources/js/components/store/ProductCard.vue` (already exists)

- Component sudah ada dan siap digunakan
- Mendukung props: product, detailUrl, mode (grid/list)
- Sudah include format harga Rupiah, image handling, availability badge
- No changes needed, hanya pastikan props sesuai dengan ProductResource

## Testing

### 6. Create Feature Test

File: `tests/Feature/ProductCatalogTest.php`

- Test GET / route returns 200
- Test products ditampilkan di home page
- Test hanya produk aktif yang ditampilkan
- Test eager loading category
- Gunakan factory untuk create test data

## Key Files to Reference

- Existing model: `app/Models/Product.php` (sudah ada scope active, inStock, featured)
- Existing model: `app/Models/Category.php` (relasi sudah proper)
- Existing component: `resources/js/components/store/ProductCard.vue` (reuse)
- Database schema: products table dengan semua field yang diperlukan sudah ada

## Implementation Notes

- Follow Laravel Boost guidelines (Indonesian comments, proper type hints)
- Gunakan Wayfinder untuk route generation di Vue
- Pastikan images di-serve dari `/storage` dengan symlink
- Format harga menggunakan format Rupiah (Rp)
- Implementasi lazy loading untuk images dengan `loading="lazy"`

### To-dos

- [ ] Buat ProductController dengan method index() dan query produk aktif
- [ ] Buat ProductResource untuk format response data produk
- [ ] Update route / di web.php ke ProductController@index
- [ ] Buat/update Home.vue dengan grid layout responsive untuk katalog produk
- [ ] Buat feature test untuk product catalog display
- [ ] Run tests dan verify semua acceptance criteria terpenuhi