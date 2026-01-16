<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'customer_id'    => $this->customer_id,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,

            // ✅ totals from Cart model accessors
            'products_count' => (int) ($this->products_count ?? 0),
            'total_quantity' => (int) ($this->total_quantity ?? 0),
            'total_price'    => (float) ($this->total_price ?? 0),

            'items' => $this->whenLoaded(
                'products',
                function () use ($request) {
                    return $this->products->map(function ($product) use ($request) {
                        $data = (new ProductResource($product))->toArray($request);
                        $data['quantity'] = (int) ($product->pivot->quantity ?? 0);
                        return $data;
                    })->values();
                },
                []
            ),
        ];
    }
}
