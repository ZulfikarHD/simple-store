<!-- 4f5d9557-e967-4386-8fa4-fc217b239089 5b422526-093c-45be-a70b-30bba1bd040d -->
# Plan: Implementasi Filter Produk Berdasarkan Kategori (CUST-002)

## Context

User story CUST-002 membutuhkan implementasi fitur filter produk berdasarkan kategori dengan kriteria:

- Category navigation menu yang menampilkan semua kategori
- Filter products by selected category dengan dynamic filtering
- Show "All Products" option untuk melihat semua produk

## Existing Resources

- Model `Product` dan `Category` sudah ada dengan relasi yang lengkap
- Component `CategoryFilter.vue` sudah ada dan siap digunakan
- Component `ProductCard.vue` sudah ada untuk display produk
- Component `SearchBar.vue` sudah ada
- Design system sudah established dengan Tailwind v4 + shadcn/ui

## Implementation Steps

### 1. Backend - Product Controller & Routes

**File baru:** `app/Http/Controllers/ProductController.php`

- Method `index()` untuk list products dengan filter kategori dan search
- Support query params: `category_id`, `search`
- Eager load `category` relation
- Filter hanya produk `active`
- Return Inertia response dengan products dan categories data

**File:** `routes/web.php`

- Tambahkan route `GET /products` dengan name `products.index`
- Public route (tidak perlu auth)

### 2. Frontend - Products Page

**File baru:** `resources/js/pages/Products/Index.vue`

- Terima props: `products`, `categories`, `filters` dari backend
- Implement CategoryFilter component untuk filter by category
- Implement SearchBar component untuk search functionality
- Grid layout responsive menggunakan ProductCard component
- Empty state ketika tidak ada produk
- URL state management menggunakan Inertia preserveState

### 3. Frontend - Navigation Update

**File:** Update navigation components untuk menambahkan link ke halaman products

- Tambahkan link "Produk" di main navigation
- Update Welcome.vue jika diperlukan untuk link ke /products

### 4. Testing

**File baru:** `tests/Feature/ProductControllerTest.php`

- Test `index()` returns all active products
- Test filter by category_id
- Test search functionality
- Test combine filter category + search
- Test hanya active products yang ditampilkan
- Test eager loading category relation

**File baru:** `tests/Feature/Pages/ProductsIndexPageTest.php`

- Test halaman products dapat diakses
- Test filtering via URL parameters
- Test response contains correct data structure

### 5. Wayfinder Integration

- Generate Wayfinder routes: `php artisan wayfinder:generate`
- Import ProductController actions di pages yang membutuhkan
- Gunakan type-safe routing

## Key Technical Decisions

1. **State Management:** Menggunakan URL query parameters untuk filter state (preserveScroll, preserveState)
2. **Performance:** Eager loading category, pagination jika diperlukan di future
3. **UX:** Smooth transitions saat filtering, loading states
4. **Accessibility:** Proper ARIA labels pada filter buttons

## Files to Create

- `app/Http/Controllers/ProductController.php`
- `resources/js/pages/Products/Index.vue`
- `tests/Feature/ProductControllerTest.php`
- `tests/Feature/Pages/ProductsIndexPageTest.php`

## Files to Modify

- `routes/web.php`
- Navigation components (AppHeader.vue atau NavMain.vue)

## Acceptance Criteria Verification

- ✓ Category navigation menu: CategoryFilter component dengan "Semua" button
- ✓ Filter products by selected category: Backend filtering + frontend state management
- ✓ Show "All Products" option: "Semua" button di CategoryFilter

### To-dos

- [ ] Buat ProductController dengan method index() dan tambahkan route di web.php
- [ ] Buat halaman Products/Index.vue dengan CategoryFilter dan ProductCard integration
- [ ] Update navigation components untuk menambahkan link ke halaman products
- [ ] Buat comprehensive tests untuk ProductController dan Products page
- [ ] Generate Wayfinder routes dan verify type-safe routing