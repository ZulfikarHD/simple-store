<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola tampilan produk pada halaman customer
 * termasuk katalog produk dengan filter kategori, pencarian, dan detail produk
 */
class ProductController extends Controller
{
    /**
     * Menampilkan halaman katalog produk dengan daftar produk aktif
     * yang dapat difilter berdasarkan kategori, dicari berdasarkan nama/deskripsi,
     * dan diurutkan berdasarkan produk terbaru
     *
     * @param  Request  $request  HTTP request dengan optional query params 'category' dan 'search'
     */
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

    /**
     * Menampilkan halaman detail produk dengan informasi lengkap
     * termasuk kategori dan produk terkait dari kategori yang sama
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

        // Load produk terkait dari kategori yang sama (exclude current product)
        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('category')
            ->limit(4)
            ->latest()
            ->get();

        return Inertia::render('ProductDetail', [
            'product' => new ProductResource($product),
            'relatedProducts' => ProductResource::collection($relatedProducts),
        ]);
    }
}
