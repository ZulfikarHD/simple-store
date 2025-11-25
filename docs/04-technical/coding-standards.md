# Coding Standards - F&B Web App

Dokumen ini menjelaskan standar penulisan kode yang harus diikuti oleh semua developer.

---

## PHP / Laravel Standards

### PSR-12 Compliance

Semua kode PHP harus mengikuti **PSR-12** coding standard dengan konvensi Laravel.

### Naming Conventions

**Classes**: PascalCase
```php
class ProductController
class OrderService
class StoreProductRequest
```

**Methods & Functions**: camelCase
```php
public function createProduct()
public function getActiveOrders()
private function calculateTotal()
```

**Variables**: camelCase
```php
$productName = 'Es Kopi';
$totalAmount = 50000;
$isAvailable = true;
```

**Constants**: UPPER_SNAKE_CASE
```php
const MAX_UPLOAD_SIZE = 5242880;
const ORDER_STATUS_PENDING = 'pending';
```

**Database Tables**: plural, snake_case
```
products, order_items, product_categories
```

**Database Columns**: snake_case
```
product_name, created_at, is_available
```

---

## Controller Standards

### Keep Controllers Thin

Controllers hanya menangani HTTP request/response. Logika bisnis ada di Service classes.

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    /**
     * Constructor dengan dependency injection untuk ProductService
     * yang menangani logika bisnis terkait produk
     */
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Menyimpan produk baru ke database
     * dengan validasi melalui Form Request
     *
     * @param StoreProductRequest $request - Request tervalidasi
     * @return \Inertia\Response redirect ke halaman produk
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }
}
```

### Use Form Requests

Selalu gunakan Form Request untuk validasi:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Menentukan apakah user terautentikasi
     * untuk melakukan request ini
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules validasi untuk pembuatan produk baru
     * dengan format array untuk konsistensi
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_available' => ['boolean'],
        ];
    }

    /**
     * Custom error messages dalam Bahasa Indonesia
     * untuk user experience yang lebih baik
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi',
            'price.required' => 'Harga produk wajib diisi',
            'price.min' => 'Harga tidak boleh negatif',
            'category_id.exists' => 'Kategori tidak valid',
        ];
    }
}
```

---

## Model Standards

### Use Casts Method

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image',
        'category_id',
        'is_available',
    ];

    /**
     * Define attribute casting untuk type safety
     * menggunakan method casts() sesuai Laravel 12
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke Category model
     * satu Product dimiliki oleh satu Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke OrderItem model
     * satu Product dapat memiliki banyak OrderItem
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
```

### Define Relationships with Return Types

Selalu gunakan return type untuk relationships:

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function items(): HasMany
{
    return $this->hasMany(OrderItem::class);
}
```

---

## Service Class Standards

```php
<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Membuat produk baru dengan handling image upload
     * menggunakan database transaction untuk data integrity
     *
     * @param array<string, mixed> $data - Data produk tervalidasi
     * @return Product - Model produk yang baru dibuat
     * @throws \Exception jika terjadi error saat penyimpanan
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // Generate slug dari nama produk
            $data['slug'] = Str::slug($data['name']);

            // Handle image upload jika ada
            if (isset($data['image'])) {
                $data['image'] = $this->uploadImage($data['image']);
            }

            return Product::create($data);
        });
    }

    /**
     * Upload gambar produk ke storage
     * dengan naming convention yang konsisten
     *
     * @param \Illuminate\Http\UploadedFile $image - File gambar
     * @return string - Path gambar yang tersimpan
     */
    private function uploadImage($image): string
    {
        $filename = Str::uuid() . '.' . $image->extension();

        return $image->storeAs('products', $filename, 'public');
    }
}
```

---

## Vue.js / Frontend Standards

### Component Structure

```vue
<script setup lang="ts">
/**
 * ProductCard Component
 * Menampilkan informasi produk dalam format card
 * dengan action untuk add to cart
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { show } from '@/actions/App/Http/Controllers/ProductController'

// Props definition dengan TypeScript
interface Props {
    product: {
        id: number
        name: string
        slug: string
        price: number
        image: string | null
        category: {
            id: number
            name: string
        }
        is_available: boolean
    }
}

const props = defineProps<Props>()

// Emits definition
const emit = defineEmits<{
    addToCart: [productId: number]
}>()

// Computed properties
const formattedPrice = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.product.price)
})

// Methods
function handleAddToCart() {
    if (!props.product.is_available) return
    emit('addToCart', props.product.id)
}
</script>

<template>
    <article
        class="group bg-white rounded-xl overflow-hidden shadow-card hover:shadow-card-hover transition-all duration-300"
    >
        <!-- Product Image -->
        <Link :href="show.url(product.slug)" class="block relative aspect-square overflow-hidden">
            <img
                v-if="product.image"
                :src="`/storage/${product.image}`"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            />
            <div v-else class="w-full h-full bg-neutral-100 flex items-center justify-center">
                <span class="text-neutral-400">No Image</span>
            </div>

            <!-- Availability Badge -->
            <span
                v-if="!product.is_available"
                class="absolute top-3 left-3 px-2 py-1 bg-error-500 text-white text-xs font-medium rounded-full"
            >
                Habis
            </span>
        </Link>

        <!-- Product Info -->
        <div class="p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-neutral-500 mb-1">
                {{ product.category.name }}
            </p>

            <h3 class="text-lg font-semibold text-neutral-900 mb-2 line-clamp-2">
                {{ product.name }}
            </h3>

            <div class="flex items-center justify-between">
                <p class="text-lg font-bold text-primary-600">
                    {{ formattedPrice }}
                </p>

                <button
                    type="button"
                    :disabled="!product.is_available"
                    class="p-2 rounded-full bg-primary-50 hover:bg-primary-100 text-primary-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="handleAddToCart"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </article>
</template>
```

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Component Files | PascalCase | `ProductCard.vue` |
| Page Components | PascalCase | `Products/Index.vue` |
| Composables | camelCase with "use" | `useCart.ts` |
| Props | camelCase | `productName` |
| Events | camelCase | `@addToCart` |
| CSS Classes | kebab-case | `product-card` |

---

## TypeScript Standards

### Type Definitions

```typescript
// types/models.ts

export interface Product {
    id: number
    name: string
    slug: string
    description: string | null
    price: number
    image: string | null
    category_id: number
    is_available: boolean
    created_at: string
    updated_at: string
    category?: Category
}

export interface Category {
    id: number
    name: string
    slug: string
    products_count?: number
}

export interface CartItem {
    product: Product
    quantity: number
}

export interface Order {
    id: number
    order_number: string
    customer_name: string
    customer_phone: string
    customer_address: string
    notes: string | null
    status: OrderStatus
    total: number
    items: OrderItem[]
    created_at: string
}

export type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'delivered' | 'cancelled'
```

---

## Git Commit Standards

### Format

```
type(scope): subject

[optional body]

[optional footer]
```

### Types

| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Code formatting |
| `refactor` | Code refactoring |
| `test` | Adding tests |
| `chore` | Maintenance tasks |

### Examples

```bash
feat(products): add product image upload feature
fix(cart): resolve quantity update issue
docs(readme): update installation instructions
refactor(orders): extract order calculation to service
test(products): add unit tests for ProductService
```

---

## Comment Standards (Bahasa Indonesia)

### PHPDoc Blocks

```php
/**
 * Menghitung total harga pesanan termasuk ongkos kirim
 * dengan mempertimbangkan diskon yang berlaku
 *
 * @param Order $order - Model order yang akan dihitung
 * @param float $deliveryFee - Biaya pengiriman (default 0)
 * @return float Total harga dalam Rupiah
 */
public function calculateTotal(Order $order, float $deliveryFee = 0): float
{
    // ...
}
```

### Inline Comments

```php
// Validasi stok produk sebelum menambahkan ke keranjang
// untuk memastikan ketersediaan item

// Query dengan eager loading untuk menghindari N+1 problem
// dimana relasi category dan items di-load bersamaan
```

---

## Code Review Checklist

- [ ] Kode mengikuti PSR-12 standards
- [ ] Tests sudah ditulis dan passing
- [ ] Dokumentasi/comments sudah ditambahkan
- [ ] Tidak ada hardcoded credentials
- [ ] Error handling sudah proper
- [ ] Database queries sudah optimized (no N+1)
- [ ] Security best practices diikuti
- [ ] Responsive design verified (jika frontend)

---

*Document version: 1.0*  
*Last updated: November 2024*

