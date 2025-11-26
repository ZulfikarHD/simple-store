<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ProductController untuk mengelola CRUD produk di admin panel
 * dengan fitur pagination, search, filter, dan image upload
 */
class ProductController extends Controller
{
    /**
     * Constructor dengan dependency injection ProductService dan CategoryService
     */
    public function __construct(
        public ProductService $productService,
        public CategoryService $categoryService
    ) {}

    /**
     * Menampilkan daftar produk dengan pagination, search, dan filter, yaitu:
     * - Pagination dengan 10 item per halaman
     * - Search berdasarkan nama produk
     * - Filter berdasarkan kategori dan status
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'is_active', 'is_featured', 'per_page']);
        $products = $this->productService->getFilteredProducts($filters);
        $categories = $this->categoryService->getActiveCategories();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Menampilkan form untuk membuat produk baru
     * dengan dropdown kategori yang tersedia
     */
    public function create(): Response
    {
        $categories = $this->categoryService->getActiveCategories();

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Menyimpan produk baru ke database
     * dengan handling image upload
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Set default values untuk checkbox jika tidak dikirim
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        $this->productService->createProduct($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit produk dengan data yang sudah terisi
     */
    public function edit(Product $product): Response
    {
        $product->load('category');
        $categories = $this->categoryService->getActiveCategories();

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Mengupdate produk di database
     * dengan handling image upload jika ada file baru
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        // Set default values untuk checkbox jika tidak dikirim
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        $this->productService->updateProduct($product, $data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Menghapus produk dari database
     * dengan validasi constraint (tidak bisa hapus jika ada di order aktif)
     */
    public function destroy(Product $product): RedirectResponse
    {
        $result = $this->productService->deleteProduct($product);

        if (! $result['success']) {
            return redirect()
                ->route('admin.products.index')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', $result['message']);
    }
}
