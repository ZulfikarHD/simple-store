<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk transformasi data Product ke format JSON
 * yang digunakan pada halaman katalog produk customer
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Mengubah data produk menjadi format yang sesuai untuk frontend
     * dengan penambahan field is_available dan stock_status untuk indikator stok
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'image' => $this->image,
            'stock' => $this->stock,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'is_available' => $this->isAvailable(),
            'stock_status' => $this->stock_status,
        ];
    }
}
