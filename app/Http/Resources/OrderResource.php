<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'order_code'            => $this->order_code,
            'status'                => $this->status,
            'customer'              => new CustomerResource($this->whenLoaded('customer')),
            'products'              => ProductResource::collection($this->whenLoaded('products')),
            'total'                 => $this->total,
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
            'darb_assabil_shipment' => new DarbAssabilShipmentResource($this->whenLoaded('darbAssabilShipment')),
        ];
    }
}
