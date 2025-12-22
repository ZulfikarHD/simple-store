<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\AuditLog;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CategoryController untuk mengelola CRUD kategori di admin panel
 * dengan fitur inline editing dan product count
 */
class CategoryController extends Controller
{
    /**
     * Constructor dengan dependency injection CategoryService
     */
    public function __construct(public CategoryService $categoryService) {}

    /**
     * Menampilkan daftar kategori dengan product count
     * diurutkan berdasarkan sort_order
     */
    public function index(): Response
    {
        $categories = $this->categoryService->getAllCategories();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Menampilkan form untuk membuat kategori baru
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Menyimpan kategori baru ke database
     * dengan audit logging untuk tracking pembuatan kategori
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Set default value untuk is_active jika tidak dikirim
        $data['is_active'] = $request->boolean('is_active', true);

        $category = $this->categoryService->createCategory($data);

        // Audit log untuk category creation
        AuditLog::log(
            action: 'category.create',
            modelType: Category::class,
            modelId: $category->id,
            newValues: $category->toArray()
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kategori dengan data yang sudah terisi
     */
    public function edit(Category $category): Response
    {
        $category->loadCount('products');

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Mengupdate kategori di database
     * dengan audit logging untuk tracking perubahan data
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $oldValues = $category->toArray();
        $data = $request->validated();

        // Set default value untuk is_active jika tidak dikirim
        $data['is_active'] = $request->boolean('is_active', true);

        $this->categoryService->updateCategory($category, $data);

        // Audit log untuk category update
        AuditLog::log(
            action: 'category.update',
            modelType: Category::class,
            modelId: $category->id,
            oldValues: $oldValues,
            newValues: $category->fresh()->toArray()
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus kategori dari database
     * dengan validasi constraint dan audit logging untuk tracking deletion
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Simpan data sebelum dihapus untuk audit log
        $categoryData = $category->toArray();

        $result = $this->categoryService->deleteCategory($category);

        if (! $result['success']) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', $result['message']);
        }

        // Audit log untuk category deletion
        AuditLog::log(
            action: 'category.delete',
            modelType: Category::class,
            modelId: $categoryData['id'],
            oldValues: $categoryData
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', $result['message']);
    }
}
