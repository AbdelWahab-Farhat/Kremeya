<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'sku'           => $this->sku,
            'name'          => $this->name,
            'description'   => $this->description,
            'selling_price' => (double) $this->selling_price,
            'buying_price'  => (double) $this->buying_price,
            'is_active'     => $this->is_active,
            'stock'         => $this->stock,
            'unavailable'   => $this->stock <= 0,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'images'        => $this->whenLoaded(
                'images',
                fn() => ImageResource::collection($this->images),
                []
            ),
        ];
    }
}
