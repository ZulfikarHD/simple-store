<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola tampilan produk pada halaman customer
 * termasuk katalog produk dan detail produk
 */
class ProductController extends Controller
{
    /**
     * Menampilkan halaman katalog produk dengan daftar semua produk aktif
     * yang diurutkan berdasarkan produk terbaru
     */
    public function index(): Response
    {
        $products = Product::query()
            ->active()
            ->with('category')
            ->latest()
            ->get();

        return Inertia::render('Home', [
            'products' => ProductResource::collection($products),
        ]);
    }
}
