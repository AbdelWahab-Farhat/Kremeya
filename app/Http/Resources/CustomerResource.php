<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'phone'      => $this->user->phone ?? null,
            'name'       => $this->user->name ?? null,
            'city'       => $this->city->name ?? null,
            'region'     => $this->region->name ?? null,
            'gender'     => $this->gender,
            'wallet'     => $this->whenLoaded('wallet', fn() => [
                'id'      => $this->wallet->id,
                'balance' => (float) $this->wallet->balance,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
