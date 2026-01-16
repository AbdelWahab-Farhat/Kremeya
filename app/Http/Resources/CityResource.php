<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"          => $this->id,
            "name"        => $this->name,
            "is_required" => $this->is_required,
            "is_active"   => $this->is_active,
            "regions"     => RegionResource::collection($this->whenLoaded('regions')),
            'created_at'  => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
