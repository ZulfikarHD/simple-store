# Products API Documentation

**Author:** Zulfikar Hidayatullah  
**Last Updated:** 2024-11-25  
**Version:** 1.0.0

---

## Overview

Products API merupakan endpoint untuk mengelola data produk F&B yang bertujuan untuk menyediakan akses ke katalog produk, yaitu: list products, detail product, dan management operations (admin only).

---

## Base URL

```
Production: https://your-domain.com
Development: http://localhost:8000
```

---

## Endpoints

### 1. Get Product Catalog

Mengambil daftar semua produk aktif dengan pagination dan filtering support untuk ditampilkan pada halaman customer.

#### Request

```http
GET /
Accept: application/json
X-Inertia: true
```

#### Query Parameters

Saat ini tidak ada query parameters. Future enhancements akan include:
- `category` - Filter by category ID
- `search` - Search by product name
- `sort` - Sort by (price, name, created_at)
- `per_page` - Items per page (default: all)

#### Response (Inertia)

```json
{
  "component": "Home",
  "props": {
    "products": {
      "data": [
        {
          "id": 1,
          "name": "Nasi Goreng Spesial",
          "slug": "nasi-goreng-spesial",
          "description": "Nasi goreng dengan telur, ayam, dan sayuran segar",
          "price": 25000,
          "image": "products/nasi-goreng.jpg",
          "category": {
            "id": 1,
            "name": "Makanan Berat"
          },
          "is_available": true
        }
      ]
    }
  },
  "url": "/",
  "version": "...",
  "encryptHistory": true,
  "clearHistory": false
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Product ID (Primary Key) |
| `name` | string | Product name |
| `slug` | string | URL-friendly product name |
| `description` | string\|null | Product description |
| `price` | float | Product price dalam Rupiah |
| `image` | string\|null | Image path relative ke storage |
| `category` | object\|null | Category information (eager loaded) |
| `category.id` | integer | Category ID |
| `category.name` | string | Category name |
| `is_available` | boolean | Availability status (based on is_active && stock > 0) |

#### Status Codes

| Code | Description |
|------|-------------|
| 200 | Success - Products retrieved |
| 500 | Server Error |

#### Example Request (cURL)

```bash
curl -X GET "http://localhost:8000/" \
  -H "Accept: application/json" \
  -H "X-Inertia: true" \
  -H "X-Requested-With: XMLHttpRequest"
```

#### Example Request (JavaScript)

```javascript
import { router } from '@inertiajs/vue3'

// Using Inertia router
router.visit('/', {
  method: 'get',
  preserveState: false,
})

// Or using Wayfinder
import { home } from '@/routes'

router.visit(home())
```

#### Business Rules

1. **Active Products Only**
   - Hanya produk dengan `is_active = true` yang ditampilkan
   - Produk inactive tidak accessible via public endpoint

2. **Availability Logic**
   - `is_available = true` jika `is_active = true` AND `stock > 0`
   - `is_available = false` jika salah satu kondisi tidak terpenuhi

3. **Category Eager Loading**
   - Category information di-load untuk menghindari N+1 queries
   - Jika category deleted, product masih ditampilkan dengan `category = null`

4. **Ordering**
   - Default order: `latest()` (newest first)
   - Based on `created_at` DESC

---

## Data Models

### Product Resource Schema

```typescript
interface Product {
  id: number
  name: string
  slug: string
  description: string | null
  price: number
  image: string | null
  category: Category | null
  is_available: boolean
}

interface Category {
  id: number
  name: string
}

interface ProductCollection {
  data: Product[]
}
```

---

## Error Handling

### Error Response Format

```json
{
  "message": "Error message",
  "exception": "ExceptionClass",
  "file": "/path/to/file.php",
  "line": 123,
  "trace": [...]
}
```

### Common Errors

#### 500 Internal Server Error

**Cause:**
- Database connection failed
- Uncaught exception dalam controller
- Missing required relationships

**Resolution:**
- Check database connection
- Review server logs
- Ensure migrations are run

---

## Performance Considerations

### Query Optimization

1. **Eager Loading**
```php
Product::query()
    ->active()
    ->with('category')  // Prevent N+1
    ->latest()
    ->get();
```

2. **Indexed Columns**
- `is_active` column indexed untuk fast filtering
- `category_id` indexed untuk join performance

3. **Resource Transformation**
- Lazy transformation hanya untuk displayed data
- Minimal data transfer ke frontend

### Caching Strategy

Saat ini belum implemented. Future enhancements:
- Cache product list untuk 5 minutes
- Invalidate on product update/create/delete
- Use Redis untuk distributed cache

```php
// Future implementation
$products = Cache::remember('products.active', 300, function () {
    return Product::query()
        ->active()
        ->with('category')
        ->latest()
        ->get();
});
```

---

## Testing

### Feature Test Example

```php
public function test_active_products_are_displayed(): void
{
    $category = Category::factory()->create();
    Product::factory()
        ->count(3)
        ->for($category)
        ->create(['is_active' => true]);

    $response = $this->get('/');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Home')
        ->has('products.data', 3)
    );
}
```

### Test Coverage

- ✅ Home page returns successful response
- ✅ Active products displayed
- ✅ Inactive products not displayed
- ✅ Product data structure validation
- ✅ Category eager loading
- ✅ Empty state handling
- ✅ Availability logic based on stock

---

## Security

### Access Control

- **Public Endpoint:** No authentication required
- **Data Filtering:** Only active products exposed
- **XSS Protection:** All data sanitized via Vue template
- **SQL Injection:** Protected by Eloquent query builder

### Rate Limiting

Saat ini tidak ada rate limiting. Recommended untuk production:
```php
// Future: Apply rate limiting
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
});
```

---

## Changelog

### Version 1.0.0 (2024-11-25)

**Added:**
- Initial product catalog endpoint
- ProductResource transformation
- Active product filtering
- Category eager loading
- Availability status computation

**Upcoming:**
- Pagination support (v1.1.0)
- Category filtering (v1.1.0 - CUST-002)
- Search functionality (v1.2.0 - CUST-003)
- Sorting options (v1.2.0)

---

## Related Documentation

- [Product Model](../../04-technical/database-schema.md#products)
- [Category Model](../../04-technical/database-schema.md#categories)
- [ProductResource](../../04-technical/api-resources.md#productresource)
- [Sprint 1 Documentation](../../07-development/sprint-planning/sprint-1-cust-001.md)

---

## Support

Untuk pertanyaan atau issue terkait Products API:
- **Developer:** Zulfikar Hidayatullah
- **Documentation:** [docs/](../../)
- **Source Code:** `app/Http/Controllers/ProductController.php`

