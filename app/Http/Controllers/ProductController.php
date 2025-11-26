<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola tampilan produk pada halaman customer
 * termasuk katalog produk dengan filter kategori, pencarian, dan detail produk
 * dengan implementasi caching untuk optimasi performa
 *
 * @author Zulfikar Hidayatullah
 */
class ProductController extends Controller
{
    /**
     * Cache TTL dalam detik (5 menit)
     */
    private const CACHE_TTL = 300;

    /**
     * Menampilkan halaman katalog produk dengan daftar produk aktif
     * yang dapat difilter berdasarkan kategori, dicari berdasarkan nama/deskripsi,
     * dan diurutkan berdasarkan produk terbaru dengan implementasi caching
     *
     * @param  Request  $request  HTTP request dengan optional query params 'category' dan 'search'
     */
    public function index(Request $request): Response
    {
        $categoryId = $request->query('category');
        $searchQuery = $request->query('search');

        // Load kategori dengan caching (jarang berubah)
        $categories = Cache::remember('categories.active', self::CACHE_TTL, function () {
            return Category::query()
                ->active()
                ->ordered()
                ->withCount(['products' => fn ($query) => $query->active()])
                ->get();
        });

        // Produk dengan caching berdasarkan filter
        // Tidak cache jika ada search query (hasil pencarian bervariasi)
        if ($searchQuery) {
            $products = Product::query()
                ->active()
                ->with('category')
                ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                ->search($searchQuery)
                ->latest()
                ->get();
        } else {
            $cacheKey = $categoryId
                ? "products.category.{$categoryId}"
                : 'products.all';

            $products = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($categoryId) {
                return Product::query()
                    ->active()
                    ->with('category')
                    ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                    ->latest()
                    ->get();
            });
        }

        return Inertia::render('Home', [
            'products' => ProductResource::collection($products),
            'categories' => CategoryResource::collection($categories),
            'selectedCategory' => $categoryId ? (int) $categoryId : null,
            'searchQuery' => $searchQuery,
        ]);
    }

    /**
     * Menampilkan halaman detail produk dengan informasi lengkap
     * termasuk kategori dan produk terkait dari kategori yang sama
     * dengan caching untuk produk terkait
     *
     * @param  Product  $product  Instance produk dari route model binding dengan slug
     */
    public function show(Product $product): Response
    {
        // Pastikan produk aktif, jika tidak return 404
        if (! $product->is_active) {
            abort(404);
        }

        // Load relasi category untuk breadcrumb dan informasi kategori
        $product->load('category');

        // Load produk terkait dengan caching
        $cacheKey = "products.related.{$product->category_id}.except.{$product->id}";
        $relatedProducts = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($product) {
            return Product::query()
                ->active()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with('category')
                ->limit(4)
                ->latest()
                ->get();
        });

        return Inertia::render('ProductDetail', [
            'product' => new ProductResource($product),
            'relatedProducts' => ProductResource::collection($relatedProducts),
        ]);
    }

    /**
     * Clear product-related cache
     * Dipanggil saat produk diupdate/delete di admin
     */
    public static function clearProductCache(): void
    {
        Cache::forget('categories.active');
        Cache::forget('products.all');

        // Clear category-specific caches
        $categories = Category::pluck('id');
        foreach ($categories as $categoryId) {
            Cache::forget("products.category.{$categoryId}");
        }
    }
}
