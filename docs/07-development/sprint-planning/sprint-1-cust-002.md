# Sprint 1 - CUST-002: Category Filter

**Author:** Zulfikar Hidayatullah  
**Sprint:** Sprint 1 (Week 1-2)  
**Story Points:** 3  
**Status:** ✅ Completed  
**Date:** 2024-11-25

---

## Overview

CUST-002 merupakan implementasi kedua dari Epic 2: Customer-Facing Features, yaitu: menambahkan fitur filter produk berdasarkan kategori dengan navigation menu horizontal dan opsi "Semua Produk" pada halaman home.

## User Story

```
As a customer, I want to filter products by category
so I can find specific types of products easily
```

## Acceptance Criteria

- ✅ Category navigation menu yang menampilkan semua kategori aktif
- ✅ Filter products by selected category
- ✅ Show "All Products" option untuk menampilkan semua produk
- ✅ Query parameter untuk maintain filter state pada URL

---

## Technical Implementation

### Backend Architecture

#### 1. CategoryResource

File: `app/Http/Resources/CategoryResource.php`

API Resource untuk transformasi data Category ke format JSON yang digunakan pada filter kategori di halaman katalog produk dengan penambahan field `products_count` untuk jumlah produk aktif, yaitu:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'description' => $this->description,
        'products_count' => $this->whenCounted('products'),
    ];
}
```

**Data Structure:**
- Field essential dari model Category (id, name, slug, description)
- `products_count` menggunakan `whenCounted()` untuk conditional loading
- Optimal untuk category navigation dengan product count badge

#### 2. ProductController Update

File: `app/Http/Controllers/ProductController.php`

Controller diperbarui untuk mendukung filter kategori dengan method `index()` yang menerima optional query parameter `category`, yaitu:

```php
public function index(Request $request): Response
{
    $categoryId = $request->query('category');

    // Load semua kategori aktif dengan jumlah produk untuk navigation menu
    $categories = Category::query()
        ->active()
        ->ordered()
        ->withCount(['products' => fn ($query) => $query->active()])
        ->get();

    // Load produk dengan optional filter kategori
    $products = Product::query()
        ->active()
        ->with('category')
        ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
        ->latest()
        ->get();

    return Inertia::render('Home', [
        'products' => ProductResource::collection($products),
        'categories' => CategoryResource::collection($categories),
        'selectedCategory' => $categoryId ? (int) $categoryId : null,
    ]);
}
```

**Key Features:**
- Query parameter `category` untuk filter produk
- Categories di-load dengan `active()` dan `ordered()` scopes
- `withCount()` dengan constraint untuk hitung produk aktif saja
- `when()` clause untuk conditional filtering
- Pass `categories`, `products`, dan `selectedCategory` ke frontend

#### 3. Category Model Scopes

File: `app/Models/Category.php`

Model Category telah memiliki scopes yang diperlukan untuk filtering dan ordering:

```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function scopeOrdered($query)
{
    return $query->orderBy('sort_order')->orderBy('name');
}
```

**Scope Features:**
- `active()` - Filter kategori dengan `is_active = true`
- `ordered()` - Urutkan berdasarkan `sort_order` kemudian `name`

### Frontend Architecture

#### 1. Home.vue Component Update

File: `resources/js/pages/Home.vue`

Halaman katalog produk diperbarui untuk mengintegrasikan CategoryFilter component dengan props baru dan navigation handling:

**Updated Props Interface:**
```typescript
interface Category {
    id: number
    name: string
    slug: string
    description: string | null
    products_count: number
}

interface CategoryCollection {
    data: Category[]
}

interface Props {
    products: ProductCollection
    categories: CategoryCollection
    selectedCategory: number | null
}
```

**Category Selection Handler:**
```typescript
function handleCategorySelect(categoryId: number | null) {
    router.visit('/', {
        data: categoryId ? { category: categoryId } : {},
        preserveState: true,
        preserveScroll: true,
    })
}
```

**Dynamic Page Title & Description:**
```typescript
const selectedCategoryName = computed(() => {
    if (!props.selectedCategory) return null
    const category = props.categories.data.find(c => c.id === props.selectedCategory)
    return category?.name ?? null
})

const pageTitle = computed(() => {
    return selectedCategoryName.value
        ? `${selectedCategoryName.value} - Katalog Produk`
        : 'Katalog Produk'
})
```

**Layout Structure:**
```
Header (Sticky)
├── Logo & Brand
└── Auth Navigation

Main Content
├── Page Title & Description (Dynamic)
├── Category Filter (CategoryFilter Component)
├── Products Grid (Filtered)
│   └── ProductCard Components
└── Empty State (Dynamic message)

Footer
└── Copyright & Author
```

#### 2. CategoryFilter Component

File: `resources/js/components/store/CategoryFilter.vue`

Component yang sudah ada digunakan untuk menampilkan filter kategori dalam bentuk tabs horizontal:

**Props Interface:**
```typescript
interface Category {
    id: number
    name: string
    slug: string
    products_count?: number
}

interface Props {
    categories: Category[]
    activeCategory?: number | null
}
```

**Events:**
```typescript
const emit = defineEmits<{
    select: [categoryId: number | null]
}>()
```

**Features:**
- Button "Semua" untuk menampilkan semua produk
- Category buttons dengan active state styling
- Products count badge (optional)
- Horizontal scrollable untuk mobile
- Hidden scrollbar dengan CSS

#### 3. Data Flow

```
User clicks category button
    ↓ (Emit event)
CategoryFilter emits 'select' event
    ↓ (Handler)
handleCategorySelect(categoryId)
    ↓ (Inertia navigation)
router.visit('/', { data: { category: categoryId } })
    ↓ (Server request)
GET /?category=1
    ↓ (Controller)
ProductController::index($request)
    ↓ (Query with filter)
Product::where('category_id', $categoryId)->get()
    ↓ (Inertia response)
{
    component: 'Home',
    props: {
        products: { data: [...filtered] },
        categories: { data: [...] },
        selectedCategory: 1
    }
}
    ↓ (Vue re-render)
Home.vue with filtered products
```

---

## Testing Strategy

### Feature Tests

File: `tests/Feature/CategoryFilterTest.php`

Comprehensive test suite dengan 11 test cases untuk memverifikasi semua acceptance criteria dan edge cases, antara lain:

1. **test_home_page_displays_active_categories**
   - Memastikan kategori aktif ditampilkan
   - Test dengan 3 aktif, 2 tidak aktif

2. **test_inactive_categories_are_not_displayed**
   - Kategori dengan `is_active = false` tidak ditampilkan
   - Verifikasi filtering logic

3. **test_category_data_has_correct_structure**
   - Verifikasi structure dari CategoryResource
   - Memastikan semua field ada (id, name, slug, description, products_count)

4. **test_category_shows_correct_active_products_count**
   - Hanya produk aktif yang dihitung
   - Test dengan 5 aktif, 2 tidak aktif

5. **test_products_can_be_filtered_by_category**
   - Filter produk via query parameter
   - Verifikasi jumlah produk yang ditampilkan

6. **test_all_products_displayed_without_category_filter**
   - Semua produk ditampilkan tanpa filter
   - selectedCategory adalah null

7. **test_selected_category_is_null_without_filter**
   - Verifikasi selectedCategory null tanpa query param

8. **test_selected_category_contains_correct_id**
   - selectedCategory berisi ID yang benar dari query param

9. **test_categories_are_ordered_correctly**
   - Kategori diurutkan berdasarkan sort_order kemudian name
   - Test dengan 3 kategori berbeda sort_order

10. **test_filter_with_nonexistent_category_shows_empty_products**
    - Filter dengan ID kategori tidak ada
    - Menampilkan produk kosong

11. **test_home_page_works_without_categories**
    - Halaman tetap berfungsi tanpa kategori
    - Empty state handling

**Test Results:**
```
✓ 11 passed (141 assertions)
Duration: 0.62s
```

### Updated ProductCatalogTest

File: `tests/Feature/ProductCatalogTest.php`

Test existing diperbarui untuk menghandle props baru:

```php
public function test_home_page_renders_home_component(): void
{
    $response = $this->get('/');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->has('products')
        ->has('categories')
        ->has('selectedCategory')
    );
}
```

### Testing Best Practices

- Menggunakan `RefreshDatabase` trait untuk isolated tests
- `withoutVite()` untuk bypass Vite manifest saat testing
- Factory pattern untuk generate test data
- PHPUnit assertions untuk comprehensive validation
- Test naming convention: `test_[what]_[condition]`

---

## Database Schema

Tidak ada perubahan schema pada sprint ini. Menggunakan existing tables dengan scopes yang sudah tersedia.

### Query Optimization

1. **Categories Query:**
```sql
SELECT categories.*, 
       (SELECT COUNT(*) FROM products 
        WHERE products.category_id = categories.id 
        AND products.is_active = 1) as products_count
FROM categories 
WHERE is_active = 1 
ORDER BY sort_order, name
```

2. **Products Query (with filter):**
```sql
SELECT products.*, categories.*
FROM products 
LEFT JOIN categories ON products.category_id = categories.id
WHERE products.is_active = 1 
AND products.category_id = ?
ORDER BY products.created_at DESC
```

---

## Code Quality

### Laravel Pint

Code formatting telah dijalankan dengan hasil:
```
✓ All files formatted correctly
```

### Linter Status

- Backend (PHP): ✅ No errors
- Frontend (Vue): ✅ No errors (1 warning - Tailwind class optimization suggestion)

---

## Performance Considerations

### Database Optimization

1. **Indexed Columns**
   - `categories.is_active` - Indexed untuk filter query
   - `categories.sort_order` - Indexed untuk ordering
   - `products.category_id` - Foreign key dengan index
   - `products.is_active` - Indexed untuk filter query

2. **Efficient Counting**
   - `withCount()` menggunakan subquery instead of loading all products
   - Constraint pada count untuk hanya hitung produk aktif

3. **Conditional Filtering**
   - `when()` clause hanya menambahkan WHERE jika parameter ada
   - No unnecessary query overhead

### Frontend Optimization

1. **Inertia Navigation**
   - `preserveState: true` untuk maintain component state
   - `preserveScroll: true` untuk UX yang smooth
   - Partial page reload instead of full page

2. **Computed Properties**
   - Page title dan description di-compute sekali
   - Reactive updates hanya saat props berubah

3. **Horizontal Scroll**
   - CSS-only scrolling untuk category tabs
   - No JavaScript untuk scroll handling

---

## Deployment Checklist

- ✅ Backend code implemented dan tested
- ✅ Frontend component updated
- ✅ CategoryResource created
- ✅ Tests written dan passing (11/11 + 8/8)
- ✅ Code formatted dengan Pint
- ✅ Wayfinder routes generated
- ⏳ Build assets (`yarn build`) - to be done on deployment

---

## Known Issues & Limitations

### Current Limitations

1. **URL State**
   - Category filter menggunakan query parameter
   - Browser back button berfungsi untuk navigation
   - Deep linking supported

2. **No Category Images**
   - Category navigation hanya menampilkan nama
   - Images dapat ditambahkan di future sprint jika diperlukan

3. **Single Category Filter**
   - Hanya satu kategori dapat dipilih
   - Multi-category filter deferred ke future enhancement

### Future Enhancements

1. Category dengan sub-categories (nested)
2. Category images pada navigation
3. Multi-category filter
4. Category-based sorting options
5. Category description tooltip

---

## Lessons Learned

### Technical Insights

1. **withCount() dengan Constraint**
   - Dapat menambahkan constraint pada count query
   - Berguna untuk hitung hanya records yang memenuhi kondisi

2. **Inertia preserveState**
   - Penting untuk UX yang smooth
   - Component state tidak di-reset saat navigation

3. **Query Parameter Handling**
   - Laravel otomatis handle type casting
   - Perlu explicit cast ke int untuk selectedCategory

### Best Practices Applied

1. ✅ Indonesian comments dengan proper technical terms
2. ✅ Type hints untuk semua methods
3. ✅ Descriptive variable names
4. ✅ Comprehensive testing coverage
5. ✅ Following Laravel Boost guidelines
6. ✅ Consistent code formatting
7. ✅ Reusing existing components (CategoryFilter)

---

## Sprint Metrics

| Metric | Value |
|--------|-------|
| Story Points | 3 |
| Actual Hours | ~2 hours |
| Files Created | 2 (CategoryResource, CategoryFilterTest) |
| Files Modified | 3 (ProductController, Home.vue, ProductCatalogTest) |
| Lines of Code | ~250 |
| Test Coverage | 11 new tests, 141 assertions |
| Bugs Found | 0 |

---

## Next Sprint

**CUST-003:** Product Search  
- Search bar in header
- Real-time or on-submit search
- Display search results
- Story Points: 3

---

## References

- [Scrum Plan](../../../scrum.md) - User story CUST-002
- [ProductController](../../../app/Http/Controllers/ProductController.php)
- [CategoryResource](../../../app/Http/Resources/CategoryResource.php)
- [Home.vue](../../../resources/js/pages/Home.vue)
- [CategoryFilter.vue](../../../resources/js/components/store/CategoryFilter.vue)
- [CategoryFilterTest](../../../tests/Feature/CategoryFilterTest.php)

---

**Last Updated:** 2024-11-25  
**Reviewed By:** -  
**Approved By:** -



