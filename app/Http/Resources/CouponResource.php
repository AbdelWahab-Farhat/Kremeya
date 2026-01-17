<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'type'        => $this->type,
            'value'       => (float) $this->value,
            'expiry_date' => $this->expiry_date?->format('Y-m-d H:i:s'),
            'usage_limit' => $this->usage_limit,
            'used_count'  => $this->used_count,
            'is_active'   => $this->is_active,
            'is_valid'    => $this->isValid(),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
