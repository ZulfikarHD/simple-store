# Optimasi Performa - F&B Web App

Dokumentasi ini menjelaskan implementasi optimasi performa yang telah dilakukan pada aplikasi, yaitu: image optimization, lazy loading, response caching, dan responsive design untuk pengalaman pengguna yang optimal.

**Author:** Zulfikar Hidayatullah
**Last Updated:** 2025-11-26

---

## Overview

Optimasi performa merupakan bagian penting dari Sprint 6 (Enhancement & Polish) yang bertujuan untuk meningkatkan kecepatan dan responsivitas aplikasi. Implementasi mencakup beberapa area utama, antara lain:

- Image Optimization dengan resize dan compress
- Response Caching untuk data yang jarang berubah
- Lazy Loading untuk gambar produk
- Responsive Design untuk mobile devices

---

## Image Optimization

### ImageService

ImageService merupakan service class yang bertanggung jawab untuk optimasi gambar yang diupload, dengan fitur:

**Lokasi:** `app/Services/ImageService.php`

**Fitur Utama:**

| Feature | Konfigurasi | Deskripsi |
|---------|-------------|-----------|
| Max Dimension | 1200x1200px | Gambar di-resize ke max dimension |
| Quality | 80% | Kompresi JPEG/WebP dengan quality 80% |
| Thumbnail | 400x400px | Reserved untuk future thumbnail generation |

**Alur Kerja:**

1. File diupload melalui ProductController
2. ImageService menerima UploadedFile
3. Validasi file sebagai image dengan `getimagesize()`
4. Jika dimensi melebihi batas, dilakukan resize dengan mempertahankan aspect ratio
5. Image dicompress dengan quality setting
6. File disimpan ke storage dengan nama unik

**Contoh Penggunaan:**

```php
// Di ProductService
$data['image'] = $this->imageService->uploadAndOptimize($data['image'], 'products');
```

### Supported Formats

ImageService mendukung format gambar berikut:

- JPEG/JPG
- PNG (dengan transparency preservation)
- GIF
- WebP (jika PHP GD support tersedia)

---

## Response Caching

### ProductController Caching

ProductController mengimplementasikan caching untuk data produk yang jarang berubah.

**Lokasi:** `app/Http/Controllers/ProductController.php`

**Cache Strategy:**

| Data | Cache Key | TTL | Kondisi |
|------|-----------|-----|---------|
| Categories | `categories.active` | 5 menit | Selalu di-cache |
| All Products | `products.all` | 5 menit | Tanpa filter pencarian |
| Products by Category | `products.category.{id}` | 5 menit | Dengan filter kategori |
| Related Products | `products.related.{category_id}.except.{product_id}` | 5 menit | Halaman detail |

**Cache Invalidation:**

Cache otomatis di-clear saat:
- Produk baru dibuat
- Produk diupdate
- Produk dihapus

```php
// Memanggil cache clear
ProductController::clearProductCache();
```

### Search Query Handling

Pencarian **tidak di-cache** karena hasil bervariasi berdasarkan input user. Hal ini memastikan hasil pencarian selalu akurat dan up-to-date.

---

## Lazy Loading

### Native Lazy Loading

Gambar produk menggunakan native lazy loading dengan atribut `loading="lazy"`:

```vue
<img
    :src="imageUrl"
    :alt="product.name"
    loading="lazy"
    class="h-full w-full object-cover"
/>
```

### Keuntungan

- Mengurangi initial page load time
- Menghemat bandwidth untuk gambar yang tidak terlihat
- Supported oleh browser modern tanpa JavaScript

---

## Responsive Design

### Mobile-First Approach

Aplikasi menggunakan pendekatan mobile-first dengan Tailwind CSS breakpoints:

| Breakpoint | Min Width | Target Device |
|------------|-----------|---------------|
| Default | 0px | Mobile (320px+) |
| `sm` | 640px | Small tablets |
| `md` | 768px | Tablets |
| `lg` | 1024px | Laptops |
| `xl` | 1280px | Desktop |

### Touch Target Guidelines

Semua interactive elements memenuhi minimum touch target 44x44px sesuai WCAG guidelines:

```vue
<Button class="h-11 w-11">
    <!-- Minimum 44px touch target -->
</Button>
```

### Mobile-Specific Optimizations

1. **Hamburger Menu** - Navigation di-collapse ke menu pada mobile
2. **Sticky Checkout** - Button checkout sticky di bottom pada mobile
3. **Simplified Grid** - Product grid menjadi 2 kolom pada mobile
4. **Hidden Descriptions** - Deskripsi produk hidden pada mobile kecil

---

## Metrics Target

| Metric | Target | Current |
|--------|--------|---------|
| First Contentful Paint | < 1.5s | Achieved |
| Time to Interactive | < 3s | Achieved |
| Largest Contentful Paint | < 2.5s | Achieved |

---

## Recommendations untuk Improvement Lanjutan

1. **CDN Integration** - Menggunakan CDN untuk static assets
2. **HTTP/2 Push** - Server push untuk critical resources
3. **Service Worker** - Offline support dan caching strategy
4. **Image WebP Conversion** - Auto-convert ke WebP dengan fallback
5. **Database Indexing** - Optimasi query dengan proper indexes

