# Sprint 1 - CUST-003: Product Search

**Author:** Zulfikar Hidayatullah  
**Sprint:** Sprint 1 (Week 1-2)  
**Story Points:** 3  
**Status:** ✅ Completed  
**Date:** 2024-11-25

---

## Overview

CUST-003 merupakan implementasi ketiga dari Epic 2: Customer-Facing Features, yaitu: menambahkan fitur pencarian produk dengan search bar yang terintegrasi di halaman katalog produk. Fitur ini memungkinkan customer untuk mencari produk berdasarkan nama dan deskripsi dengan real-time search menggunakan debounce untuk optimasi performa.

## User Story

```
As a customer, I want to search for products
so I can quickly find what I'm looking for
```

## Acceptance Criteria

- ✅ Search bar in header yang mudah diakses
- ✅ Real-time search dengan debounce untuk optimasi performa
- ✅ Display search results dengan informasi jumlah hasil
- ✅ Query parameter untuk maintain search state pada URL
- ✅ Kombinasi search dengan category filter

---

## Technical Implementation

### Backend Architecture

#### 1. Product Model - Search Scope

File: `app/Models/Product.php`

Scope baru ditambahkan untuk pencarian produk berdasarkan nama dan deskripsi dengan case-insensitive matching menggunakan LIKE operator, yaitu:

```php
/**
 * Scope untuk pencarian produk berdasarkan nama dan deskripsi
 * dengan case-insensitive matching menggunakan LIKE operator
 *
 * @param  \Illuminate\Database\Eloquent\Builder<Product>  $query
 * @param  string  $term  Kata kunci pencarian
 * @return \Illuminate\Database\Eloquent\Builder<Product>
 */
public function scopeSearch($query, string $term)
{
    return $query->where(function ($q) use ($term) {
        $q->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    });
}
```

**Key Features:**
- Pencarian di field `name` dan `description` sekaligus
- Case-insensitive dengan LIKE operator
- Grouped WHERE clause untuk proper OR logic
- Return query builder untuk method chaining

#### 2. ProductController Update

File: `app/Http/Controllers/ProductController.php`

Controller diperbarui untuk mendukung pencarian dengan method `index()` yang menerima optional query parameter `search` selain `category`, yaitu:

```php
public function index(Request $request): Response
{
    $categoryId = $request->query('category');
    $searchQuery = $request->query('search');

    // Load semua kategori aktif dengan jumlah produk untuk navigation menu
    $categories = Category::query()
        ->active()
        ->ordered()
        ->withCount(['products' => fn ($query) => $query->active()])
        ->get();

    // Load produk dengan optional filter kategori dan pencarian
    $products = Product::query()
        ->active()
        ->with('category')
        ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
        ->when($searchQuery, fn ($query) => $query->search($searchQuery))
        ->latest()
        ->get();

    return Inertia::render('Home', [
        'products' => ProductResource::collection($products),
        'categories' => CategoryResource::collection($categories),
        'selectedCategory' => $categoryId ? (int) $categoryId : null,
        'searchQuery' => $searchQuery,
    ]);
}
```

**Key Features:**
- Query parameter `search` untuk pencarian produk
- Kombinasi dengan category filter menggunakan `when()` clause
- Pass `searchQuery` ke frontend untuk maintain input state
- Chaining search scope dengan filter lainnya

### Frontend Architecture

#### 1. Home.vue Component Update

File: `resources/js/pages/Home.vue`

Halaman katalog produk diperbarui untuk mengintegrasikan SearchBar component dengan handling pencarian dan kombinasi filter:

**Updated Props Interface:**
```typescript
interface Props {
    products: ProductCollection
    categories: CategoryCollection
    selectedCategory: number | null
    searchQuery: string | null
}
```

**Local State untuk Search Input:**
```typescript
const localSearchQuery = ref(props.searchQuery ?? '')
```

**Search Handler:**
```typescript
function handleSearch(searchTerm: string) {
    const data: Record<string, string | number> = {}
    if (searchTerm) {
        data.search = searchTerm
    }
    if (props.selectedCategory) {
        data.category = props.selectedCategory
    }
    router.visit('/', {
        data,
        preserveState: true,
        preserveScroll: true,
    })
}
```

**Clear Search Handler:**
```typescript
function handleClearSearch() {
    localSearchQuery.value = ''
    const data: Record<string, number> = {}
    if (props.selectedCategory) {
        data.category = props.selectedCategory
    }
    router.visit('/', {
        data,
        preserveState: true,
        preserveScroll: true,
    })
}
```

**Dynamic Page Content:**
```typescript
const pageTitle = computed(() => {
    if (props.searchQuery) {
        return `Hasil Pencarian "${props.searchQuery}" - Katalog Produk`
    }
    return selectedCategoryName.value
        ? `${selectedCategoryName.value} - Katalog Produk`
        : 'Katalog Produk'
})

const pageDescription = computed(() => {
    if (props.searchQuery) {
        return `Menampilkan ${props.products.data.length} hasil pencarian untuk "${props.searchQuery}"`
    }
    return selectedCategoryName.value
        ? `Temukan berbagai produk ${selectedCategoryName.value.toLowerCase()} berkualitas`
        : 'Temukan berbagai produk berkualitas untuk kebutuhan Anda'
})

const emptyStateTitle = computed(() => {
    if (props.searchQuery) {
        return `Tidak Ditemukan Hasil untuk "${props.searchQuery}"`
    }
    // ... category handling
})
```

**Layout Structure:**
```
Header (Sticky)
├── Logo & Brand
└── Auth Navigation

Main Content
├── Search Bar (SearchBar Component)
├── Page Title & Description (Dynamic)
│   └── Clear Search Button (conditional)
├── Category Filter (CategoryFilter Component)
├── Products Grid (Filtered & Searched)
│   └── ProductCard Components
└── Empty State (Dynamic - search/category aware)

Footer
└── Copyright & Author
```

#### 2. SearchBar Component

File: `resources/js/components/store/SearchBar.vue`

Component yang sudah ada digunakan untuk input pencarian dengan debounce functionality:

**Props Interface:**
```typescript
interface Props {
    modelValue?: string
    placeholder?: string
    debounce?: number
}
```

**Events:**
```typescript
const emit = defineEmits<{
    'update:modelValue': [value: string]
    search: [value: string]
}>()
```

**Features:**
- Input dengan icon search dan clear button
- Debounce functionality menggunakan `@vueuse/core`
- v-model support untuk two-way binding
- Rounded pill design dengan focus state
- Configurable debounce delay (default 300ms)

#### 3. Data Flow

```
User types in SearchBar
    ↓ (Debounced - 400ms)
SearchBar emits 'search' event
    ↓ (Handler)
handleSearch(searchTerm)
    ↓ (Inertia navigation)
router.visit('/', { data: { search: term, category: id } })
    ↓ (Server request)
GET /?search=nasi&category=1
    ↓ (Controller)
ProductController::index($request)
    ↓ (Query with search + filter)
Product::search($term)->where('category_id', $id)->get()
    ↓ (Inertia response)
{
    component: 'Home',
    props: {
        products: { data: [...filtered] },
        categories: { data: [...] },
        selectedCategory: 1,
        searchQuery: 'nasi'
    }
}
    ↓ (Vue re-render)
Home.vue with search results
```

---

## Testing Strategy

### Feature Tests

File: `tests/Feature/ProductSearchTest.php`

Comprehensive test suite dengan 11 test cases untuk memverifikasi semua acceptance criteria dan edge cases, antara lain:

1. **test_can_search_products_by_name**
   - Pencarian berdasarkan nama produk
   - Verifikasi hasil pencarian yang tepat

2. **test_can_search_products_by_description**
   - Pencarian berdasarkan deskripsi produk
   - Memastikan search scope mencakup description

3. **test_search_is_case_insensitive**
   - Pencarian tidak sensitif huruf besar/kecil
   - Test dengan input lowercase, hasil UPPERCASE

4. **test_search_combined_with_category_filter**
   - Kombinasi pencarian dengan filter kategori
   - Verifikasi kedua filter bekerja bersamaan

5. **test_empty_search_returns_all_products**
   - Pencarian kosong mengembalikan semua produk
   - Tidak ada filter yang diterapkan

6. **test_search_with_no_results_returns_empty_array**
   - Pencarian tanpa hasil
   - Verifikasi empty state handling

7. **test_search_query_is_passed_to_frontend**
   - searchQuery prop dikirim ke frontend
   - Maintain input state

8. **test_search_query_is_null_without_search**
   - searchQuery null tanpa parameter
   - Default state handling

9. **test_search_only_returns_active_products**
   - Hanya produk aktif yang ditampilkan
   - Filter is_active tetap berlaku

10. **test_search_with_partial_keyword**
    - Pencarian dengan kata kunci parsial
    - LIKE operator berfungsi dengan benar

11. **test_search_matches_both_name_and_description**
    - Pencarian match di nama dan deskripsi
    - OR logic dalam search scope

**Test Results:**
```
✓ 11 passed (121 assertions)
Duration: 0.35s
```

### Testing Best Practices

- Menggunakan `RefreshDatabase` trait untuk isolated tests
- `withoutVite()` untuk bypass Vite manifest saat testing
- Factory pattern untuk generate test data
- PHPUnit assertions untuk comprehensive validation
- Test naming convention: `test_[what]_[condition]`

---

## Database Schema

Tidak ada perubahan schema pada sprint ini. Menggunakan existing tables dengan scope baru.

### Query Optimization

**Search Query:**
```sql
SELECT products.*, categories.*
FROM products 
LEFT JOIN categories ON products.category_id = categories.id
WHERE products.is_active = 1 
AND (products.name LIKE '%term%' OR products.description LIKE '%term%')
AND products.category_id = ?
ORDER BY products.created_at DESC
```

**Index Recommendations:**
- `products.name` - Untuk pencarian nama (consider FULLTEXT untuk production)
- `products.description` - Untuk pencarian deskripsi
- Existing indexes tetap digunakan untuk filtering

---

## Code Quality

### Laravel Pint

Code formatting telah dijalankan dengan hasil:
```
✓ All files formatted correctly
```

### Linter Status

- Backend (PHP): ✅ No errors
- Frontend (Vue): ✅ No errors

---

## Performance Considerations

### Backend Optimization

1. **Debounce pada Frontend**
   - Delay 400ms sebelum request
   - Mengurangi jumlah request ke server
   - Smooth UX saat user mengetik

2. **Conditional Query Building**
   - `when()` clause hanya menambahkan WHERE jika parameter ada
   - No unnecessary query overhead

3. **LIKE Query Limitations**
   - LIKE dengan leading wildcard tidak menggunakan index
   - Untuk dataset besar, pertimbangkan FULLTEXT search atau Elasticsearch

### Frontend Optimization

1. **Inertia Navigation**
   - `preserveState: true` untuk maintain component state
   - `preserveScroll: true` untuk UX yang smooth
   - Partial page reload instead of full page

2. **Local State Management**
   - `localSearchQuery` untuk immediate UI feedback
   - Sync dengan props saat navigation complete

3. **Computed Properties**
   - Page title, description, dan empty state di-compute sekali
   - Reactive updates hanya saat props berubah

---

## Deployment Checklist

- ✅ Backend code implemented dan tested
- ✅ Frontend component updated
- ✅ Search scope added to Product model
- ✅ Tests written dan passing (11/11)
- ✅ Code formatted dengan Pint
- ✅ Wayfinder routes generated
- ⏳ Build assets (`yarn build`) - to be done on deployment

---

## Known Issues & Limitations

### Current Limitations

1. **LIKE Search Performance**
   - LIKE dengan leading wildcard tidak optimal untuk dataset besar
   - Consider FULLTEXT atau external search engine untuk scaling

2. **No Search Suggestions**
   - Tidak ada autocomplete atau suggestions
   - Deferred ke future enhancement

3. **No Search History**
   - User tidak dapat melihat pencarian sebelumnya
   - Deferred ke future enhancement

### Future Enhancements

1. Autocomplete suggestions saat mengetik
2. Search history untuk logged-in users
3. Advanced search filters (price range, availability)
4. Search result highlighting
5. FULLTEXT search untuk performa lebih baik
6. Elasticsearch integration untuk fitur search lebih advanced

---

## Lessons Learned

### Technical Insights

1. **Grouped WHERE Clause**
   - Penting untuk OR logic dalam search
   - Tanpa grouping, query bisa salah

2. **Debounce Timing**
   - 400ms adalah sweet spot untuk typing
   - Terlalu cepat = banyak request, terlalu lambat = UX buruk

3. **Query Parameter Preservation**
   - Kombinasi multiple filters perlu careful handling
   - Maintain state saat clear salah satu filter

### Best Practices Applied

1. ✅ Indonesian comments dengan proper technical terms
2. ✅ Type hints untuk semua methods
3. ✅ Descriptive variable names
4. ✅ Comprehensive testing coverage
5. ✅ Following Laravel Boost guidelines
6. ✅ Consistent code formatting
7. ✅ Reusing existing components (SearchBar)

---

## Sprint Metrics

| Metric | Value |
|--------|-------|
| Story Points | 3 |
| Actual Hours | ~1.5 hours |
| Files Created | 1 (ProductSearchTest) |
| Files Modified | 3 (Product, ProductController, Home.vue) |
| Lines of Code | ~150 |
| Test Coverage | 11 new tests, 121 assertions |
| Bugs Found | 0 |

---

## Next Sprint

**CUST-004:** Product Detail Page  
- Click product to see detail page
- Show full description, price, images
- "Add to Cart" button
- Story Points: 2

---

## References

- [Scrum Plan](../../../scrum.md) - User story CUST-003
- [ProductController](../../../app/Http/Controllers/ProductController.php)
- [Product Model](../../../app/Models/Product.php)
- [Home.vue](../../../resources/js/pages/Home.vue)
- [SearchBar.vue](../../../resources/js/components/store/SearchBar.vue)
- [ProductSearchTest](../../../tests/Feature/ProductSearchTest.php)

---

**Last Updated:** 2024-11-25  
**Reviewed By:** -  
**Approved By:** -

