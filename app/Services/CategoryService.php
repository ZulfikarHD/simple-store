<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * CategoryService untuk mengelola operasi CRUD kategori di admin panel
 * dengan fitur sorting, product count, dan image management
 */
class CategoryService
{
    /**
     * Mendapatkan semua kategori dengan product count
     * diurutkan berdasarkan sort_order kemudian nama
     *
     * @return Collection<int, Category>
     */
    public function getAllCategories(): Collection
    {
        return Category::query()
            ->withCount('products')
            ->ordered()
            ->get();
    }

    /**
     * Mendapatkan kategori aktif untuk dropdown select
     * digunakan saat membuat atau edit produk
     *
     * @return Collection<int, Category>
     */
    public function getActiveCategories(): Collection
    {
        return Category::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Mendapatkan kategori berdasarkan ID dengan product count
     */
    public function getCategoryById(int $id): Category
    {
        return Category::withCount('products')->findOrFail($id);
    }

    /**
     * Membuat kategori baru dengan handling image upload
     *
     * @param  array<string, mixed>  $data  Data kategori dari form request
     */
    public function createCategory(array $data): Category
    {
        // Set default sort_order jika tidak ada
        if (! isset($data['sort_order'])) {
            $maxOrder = Category::max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        // Handle image upload jika ada
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return Category::create($data);
    }

    /**
     * Mengupdate kategori dengan handling image upload
     *
     * @param  array<string, mixed>  $data  Data kategori dari form request
     */
    public function updateCategory(Category $category, array $data): Category
    {
        // Handle image upload jika ada file baru
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Hapus image lama jika ada
            $this->deleteImage($category->image);
            $data['image'] = $this->uploadImage($data['image']);
        } else {
            // Jika tidak ada file baru, jangan update field image
            unset($data['image']);
        }

        $category->update($data);

        return $category->fresh();
    }

    /**
     * Menghapus kategori dengan validasi constraint
     * Kategori tidak bisa dihapus jika masih memiliki produk
     *
     * @return array{success: bool, message: string}
     */
    public function deleteCategory(Category $category): array
    {
        $productsCount = $category->products()->count();

        if ($productsCount > 0) {
            return [
                'success' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki '.$productsCount.' produk.',
            ];
        }

        // Hapus image jika ada
        $this->deleteImage($category->image);

        $category->delete();

        return [
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ];
    }

    /**
     * Mengupdate urutan kategori (sort_order)
     *
     * @param  array<int, int>  $orders  Array dengan key = category_id, value = sort_order
     */
    public function updateSortOrder(array $orders): void
    {
        foreach ($orders as $categoryId => $sortOrder) {
            Category::where('id', $categoryId)->update(['sort_order' => $sortOrder]);
        }
    }

    /**
     * Upload image ke storage dengan nama unik
     */
    private function uploadImage(UploadedFile $file): string
    {
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('categories', $filename, 'public');
    }

    /**
     * Menghapus image dari storage
     */
    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
