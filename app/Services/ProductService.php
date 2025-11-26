<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * ProductService untuk mengelola operasi CRUD produk di admin panel
 * dengan fitur filtering, search, dan image upload management
 */
class ProductService
{
    /**
     * Mendapatkan daftar produk dengan pagination dan filter, yaitu:
     * - Pencarian berdasarkan nama
     * - Filter berdasarkan kategori
     * - Filter berdasarkan status aktif/tidak aktif
     *
     * @param  array<string, mixed>  $filters  Parameter filter dari request
     * @return LengthAwarePaginator<Product>
     */
    public function getFilteredProducts(array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()
            ->with('category')
            ->latest();

        // Filter pencarian berdasarkan nama
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filter berdasarkan kategori
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter berdasarkan status aktif
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filter berdasarkan status featured
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) ($filters['per_page'] ?? 10);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Mendapatkan produk berdasarkan ID dengan relasi category
     */
    public function getProductById(int $id): Product
    {
        return Product::with('category')->findOrFail($id);
    }

    /**
     * Membuat produk baru dengan handling image upload
     *
     * @param  array<string, mixed>  $data  Data produk dari form request
     */
    public function createProduct(array $data): Product
    {
        // Handle image upload jika ada
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return Product::create($data);
    }

    /**
     * Mengupdate produk dengan handling image upload
     *
     * @param  array<string, mixed>  $data  Data produk dari form request
     */
    public function updateProduct(Product $product, array $data): Product
    {
        // Handle image upload jika ada file baru
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Hapus image lama jika ada
            $this->deleteImage($product->image);
            $data['image'] = $this->uploadImage($data['image']);
        } else {
            // Jika tidak ada file baru, jangan update field image
            unset($data['image']);
        }

        $product->update($data);

        return $product->fresh(['category']);
    }

    /**
     * Menghapus produk dengan validasi constraint
     * Produk tidak bisa dihapus jika masih ada di order yang belum selesai
     *
     * @return array{success: bool, message: string}
     */
    public function deleteProduct(Product $product): array
    {
        // Cek apakah produk ada di order yang belum selesai (pending/processing)
        $activeOrderItems = $product->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['pending', 'processing', 'confirmed']);
            })
            ->count();

        if ($activeOrderItems > 0) {
            return [
                'success' => false,
                'message' => 'Produk tidak dapat dihapus karena masih ada di '.$activeOrderItems.' pesanan aktif.',
            ];
        }

        // Hapus image jika ada
        $this->deleteImage($product->image);

        $product->delete();

        return [
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ];
    }

    /**
     * Upload image ke storage dengan nama unik
     */
    private function uploadImage(UploadedFile $file): string
    {
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('products', $filename, 'public');
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
