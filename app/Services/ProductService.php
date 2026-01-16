<?php

namespace App\Services;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function getAll(array $filters = [], int $perPage = 15): AnonymousResourceCollection
    {
        $query = Product::query()->with(['images']);

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%");
            });
        }

        return ProductResource::collection(
            $query->latest()->paginate($perPage)->withQueryString()
        );
    }

    public function create(array $validated): ProductResource
    {
        $images = Arr::pull($validated, 'images', []);

        return DB::transaction(function () use ($validated, $images) {
            $product = Product::create($validated);

            if (!empty($images)) {
                $this->storeImages($product, $images, 0); // أول صورة primary
            }

            return new ProductResource($product->load('images'));
        });
    }

    public function update(Product $product, array $validated): ProductResource
    {
        // مهم: نعرف هل images انرسلت أصلاً ولا لا
        $hasImagesKey = array_key_exists('images', $validated);
        $images = Arr::pull($validated, 'images', null);

        return DB::transaction(function () use ($product, $validated, $hasImagesKey, $images) {
            $product->update($validated);

            // لو المستخدم بعث images (حتى لو فاضية) -> استبدل كل الصور
            if ($hasImagesKey) {
                $this->deleteImages($product);
                if (is_array($images) && !empty($images)) {
                    $this->storeImages($product, $images, 0);
                }
            }

            return new ProductResource($product->load('images'));
        });
    }

    private function storeImages(Product $product, array $images, int $primaryIndex = 0): void
    {
        $dir  = "products/{$product->id}";
        $disk = 'public';

        foreach ($images as $i => $file) {
            $ext = $file->getClientOriginalExtension();
            $name = (string) Str::uuid() . '.' . $ext;

            $path = $file->storeAs($dir, $name, $disk);

            $product->images()->create([
                'disk'       => $disk,
                'path'       => $path,
                'alt'        => $product->name,
                'sort_order' => $i,
                'is_primary' => ($i === $primaryIndex),
            ]);
        }
    }

    private function deleteImages(Product $product): void
    {
        $product->load('images');

        foreach ($product->images as $img) {
            $disk = $img->disk ?: 'public';
            if ($img->path) {
                Storage::disk($disk)->delete($img->path);
            }
        }

        // حذف سجلات DB (hard delete لأن Image model ما فيه SoftDeletes)
        $product->images()->delete();
    }
}
