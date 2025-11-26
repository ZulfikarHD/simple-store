<!-- f26c36d9-e29f-417a-b15d-82f264a5f0d1 8dc0eb41-17c2-427f-a16c-f802ffce6ea2 -->
# Implementasi Admin Product & Category Management

## Overview

Implementasi fitur manajemen produk dan kategori untuk admin panel F&B Web App sesuai Sprint 3, yaitu: ADMIN-002 (View Products), ADMIN-003 (Add Products), ADMIN-004 (Edit Products), ADMIN-005 (Delete Products), dan ADMIN-006 (Manage Categories).

## Arsitektur

Mengikuti pattern yang sudah ada di codebase:

- **Controller Layer**: `app/Http/Controllers/Admin/`
- **Service Layer**: `app/Services/`
- **Form Requests**: `app/Http/Requests/Admin/`
- **Vue Pages**: `resources/js/pages/Admin/`

## Implementation Steps

### 1. ADMIN-002: View All Products (3 SP)

**Backend:**

- Create `ProductService` di `app/Services/` dengan methods: `getFilteredProducts()`, `getProductById()`
- Create `ProductController` di `app/Http/Controllers/Admin/` dengan method `index()`
- Register routes di `routes/web.php`: `GET /admin/products`

**Frontend:**

- Create `Products/Index.vue` dengan fitur:
  - Data table menggunakan existing Card component
  - Pagination dengan Inertia
  - Search bar untuk nama produk
  - Filter dropdown untuk kategori dan status (active/inactive)
  - Kolom: Name, Category, Price, Stock, Status, Actions

### 2. ADMIN-003: Add New Products (5 SP)

**Backend:**

- Create `StoreProductRequest` untuk validasi form
- Add method `create()` dan `store()` di `ProductController`
- Handle image upload ke storage

**Frontend:**

- Create `Products/Create.vue` dengan form fields:
  - Name (text)
  - Description (textarea)
  - Price (number)
  - Stock (number)
  - Category (dropdown)
  - Image (file upload dengan preview)
  - Is Active (checkbox)
  - Is Featured (checkbox)
- Form validation menggunakan Inertia Form component

### 3. ADMIN-004: Edit Existing Products (3 SP)

**Backend:**

- Create `UpdateProductRequest` untuk validasi
- Add method `edit()` dan `update()` di `ProductController`

**Frontend:**

- Create `Products/Edit.vue` dengan form pre-filled data
- Reuse form structure dari Create.vue (extract ke composable/component jika memungkinkan)

### 4. ADMIN-005: Delete Products (2 SP)

**Backend:**

- Add method `destroy()` di `ProductController`
- Implementasi soft delete check: jika product ada di active orders, tolak delete
- Return proper error message

**Frontend:**

- Add delete button dengan confirmation dialog (shadcn-vue Dialog)
- Show error toast jika delete gagal karena constraint

### 5. ADMIN-006: Manage Categories (5 SP)

**Backend:**

- Create `CategoryService` di `app/Services/`
- Create `CategoryController` di `app/Http/Controllers/Admin/`
- Create `StoreCategoryRequest` dan `UpdateCategoryRequest`
- Register routes untuk categories CRUD

**Frontend:**

- Create `Categories/Index.vue` dengan inline editing atau modal form
- CRUD operations: View, Add, Edit, Delete
- Show product count per category
- Reorder capability (drag-drop jika waktu memungkinkan, atau manual sort_order)

### 6. Update Routes

```php
// routes/web.php - Admin routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
});
```

### 7. Navigation Update

- Update sidebar/navigation untuk include Products dan Categories menu items
- Gunakan icon dari lucide-vue-next

### 8. Generate Wayfinder

```bash
php artisan wayfinder:generate
```

## Testing

- Create feature tests untuk setiap controller method
- Test validation rules
- Test authorization (hanya authenticated admin yang bisa akses)

## Documentation Update

Update dokumentasi di `docs/` dengan Bahasa Indonesia formal:

- Update `docs/README.md` dengan Sprint 3 progress
- Update/create `docs/06-user-guides/admin-guide.md` untuk panduan admin
- Gunakan writing style sesuai cursor rules (yaitu:, antara lain:, dengan demikian,)

## Commit Message

Format commit message mengikuti conventional commits dengan deskripsi Bahasa Indonesia:

```
feat(admin): implementasi manajemen produk dan kategori

- Add ProductController dengan CRUD operations untuk admin panel
- Add CategoryController dengan CRUD operations
- Add ProductService dan CategoryService untuk business logic
- Add form requests untuk validasi input produk dan kategori
- Add Vue pages: Products/Index, Create, Edit dan Categories/Index
- Update admin routes dengan resource routing
- Update dokumentasi admin guide dengan panduan penggunaan

ADMIN-002, ADMIN-003, ADMIN-004, ADMIN-005, ADMIN-006
```

## File Changes Summary

| Type | Path |

|------|------|

| Create | `app/Services/ProductService.php` |

| Create | `app/Services/CategoryService.php` |

| Create | `app/Http/Controllers/Admin/ProductController.php` |

| Create | `app/Http/Controllers/Admin/CategoryController.php` |

| Create | `app/Http/Requests/Admin/StoreProductRequest.php` |

| Create | `app/Http/Requests/Admin/UpdateProductRequest.php` |

| Create | `app/Http/Requests/Admin/StoreCategoryRequest.php` |

| Create | `app/Http/Requests/Admin/UpdateCategoryRequest.php` |

| Create | `resources/js/pages/Admin/Products/Index.vue` |

| Create | `resources/js/pages/Admin/Products/Create.vue` |

| Create | `resources/js/pages/Admin/Products/Edit.vue` |

| Create | `resources/js/pages/Admin/Categories/Index.vue` |

| Modify | `routes/web.php` |

| Modify | `docs/README.md` |

| Create | `docs/06-user-guides/admin-guide.md` |

### To-dos

- [x] ADMIN-002: Implementasi view all products dengan pagination, search, dan filter
- [x] ADMIN-003: Implementasi form tambah produk baru dengan image upload
- [x] ADMIN-004: Implementasi form edit produk dengan pre-filled data
- [x] ADMIN-005: Implementasi delete produk dengan confirmation dan constraint check
- [x] ADMIN-006: Implementasi CRUD kategori dengan inline editing
- [x] Create feature tests untuk ProductController dan CategoryController
- [x] Update dokumentasi di docs/ dengan writing style Indonesia formal sesuai @documentation-structure.md 
- [x] Create commit message mengikuti conventional commits style dengan deskripsi Bahasa Indonesia