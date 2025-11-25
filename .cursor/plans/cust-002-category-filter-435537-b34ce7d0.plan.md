<!-- b34ce7d0-4c9f-4e17-84c8-1b0b5c0d9ff6 17ebf3f9-f520-47a4-9cb6-ff17adcc7f9b -->
# Plan: Implementasi CUST-002 - Category Filter

## Overview

Mengimplementasikan user story CUST-002 untuk menambahkan fitur filter produk berdasarkan kategori dengan navigation menu horizontal dan opsi "Semua Produk" pada halaman home.

## Acceptance Criteria

- Category navigation menu yang menampilkan semua kategori aktif
- Filter produk berdasarkan selected category
- Show "All Products" option untuk menampilkan semua produk
- Query parameter untuk maintain filter state pada URL

## Backend Changes

### 1. Fix Category Model Scope

File: `app/Models/Category.php` (line 83-86)

- Complete the `scopeActive()` method yang saat ini kosong
- Return `$query->where('is_active', true)`

### 2. Create CategoryResource

Command: `php artisan make:resource CategoryResource`

File: `app/Http/Resources/CategoryResource.php`

- Create API Resource untuk format data category
- Include: id, name, slug, description
- Add products_count field using `withCount('products')`

### 3. Update ProductController

File: `app/Http/Controllers/ProductController.php`

- Modify `index()` method untuk support category filtering
- Accept optional `category` query parameter
- Load active categories dengan products count: `Category::active()->ordered()->withCount('products')->get()`
- Filter products by category_id jika parameter ada: `->when($categoryId, fn($q) => $q->where('category_id', $categoryId))`
- Pass `categories` dan `selectedCategory` ke frontend

### 4. Update Routes (Optional)

File: `routes/web.php`

- Route sudah OK, tidak perlu perubahan (query param handled automatically)

## Frontend Changes

### 5. Update Home.vue Component

File: `resources/js/pages/Home.vue`

- Add import `CategoryFilter` component (sudah exists di `resources/js/components/store/CategoryFilter.vue`)
- Update Props interface untuk accept `categories` dan `selectedCategory`
- Add category filter section sebelum products grid
- Handle category selection dengan Inertia navigation: `router.visit('/', { data: { category: categoryId }, preserveState: true })`
- Update page title/description dinamis berdasarkan selected category

### 6. Verify CategoryFilter Component

File: `resources/js/components/store/CategoryFilter.vue` (already exists)

- Component sudah complete dengan semua fitur yang diperlukan
- Supports "Semua" button dan category list
- Shows products_count per category
- Emits 'select' event dengan categoryId

## Testing

### 7. Create Feature Test

File: `tests/Feature/CategoryFilterTest.php`

- Test dapat menampilkan semua kategori aktif
- Test filter produk by category
- Test "All Products" menampilkan semua produk
- Test hanya kategori aktif yang muncul
- Test query parameter maintain state

### 8. Update Existing Tests

File: `tests/Feature/ProductCatalogTest.php`

- Update test untuk handle categories prop baru

## Documentation Updates

### 9. Create Sprint Documentation

File: `docs/07-development/sprint-planning/sprint-1-cust-002.md`

- Follow format dari `sprint-1-cust-001.md`
- Include Overview, User Story, Acceptance Criteria
- Document Technical Implementation (backend + frontend)
- Include code examples
- Add Testing section
- Mark status as Completed

### 10. Update Main README

File: `docs/README.md`

- Update "Latest Feature" dari CUST-001 ke CUST-002
- Update "Project Status" line if needed

### 11. Update API Documentation

File: `docs/05-api-documentation/endpoints/products.md`

- Document new query parameter `category` untuk product listing
- Add example request/response dengan category filter

### 12. Update Component Documentation

File: `docs/03-design/ui-ux/design-system.md`

- Document CategoryFilter component usage jika belum ada
- Include props, events, dan usage example

## Commit Message

Create conventional commit message:

```
feat(catalog): implement category filter for product listing

Implement CUST-002 user story untuk filter produk berdasarkan kategori dengan fitur:

Backend:
- Fix Category::scopeActive() method di model
- Create CategoryResource untuk API transformation
- Update ProductController untuk support category filtering via query param
- Load active categories dengan products count

Frontend:
- Integrate CategoryFilter component ke Home page
- Add category navigation dengan "Semua Produk" option
- Implement Inertia navigation untuk maintain filter state
- Update props interface untuk categories dan selectedCategory

Testing:
- Add CategoryFilterTest dengan comprehensive coverage
- Update ProductCatalogTest untuk handle categories prop

Documentation:
- Create sprint-1-cust-002.md dengan complete implementation guide
- Update docs README untuk latest feature status
- Update API documentation untuk category filter endpoint
- Document CategoryFilter component usage

Story Points: 3
Acceptance Criteria: ✅ All completed
```

### To-dos

- [ ] Fix Category::scopeActive() method
- [ ] Create CategoryResource for API transformation
- [ ] Update ProductController to support category filtering
- [ ] Integrate CategoryFilter component into Home.vue
- [ ] Create CategoryFilterTest and update existing tests
- [ ] Create sprint-1-cust-002.md documentation
- [ ] Update docs/README.md with latest feature
- [ ] Update API documentation for category filter
- [ ] Document CategoryFilter component usage
- [ ] Create commit with conventional commit message