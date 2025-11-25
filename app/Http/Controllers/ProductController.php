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
 * termasuk katalog produk dengan filter kategori dan detail produk
 */
class ProductController extends Controller
{
    /**
     * Menampilkan halaman katalog produk dengan daftar produk aktif
     * yang dapat difilter berdasarkan kategori dan diurutkan berdasarkan produk terbaru
     *
     * @param  Request  $request  HTTP request dengan optional query param 'category'
     */
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
}
