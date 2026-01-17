<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type->value,
            'type_label'     => $this->type->label(),
            'amount'         => (float) $this->amount,
            'balance_before' => (float) $this->balance_before,
            'balance_after'  => (float) $this->balance_after,
            'description'    => $this->description,
            'wallet'         => $this->whenLoaded('wallet', fn() => [
                'id'       => $this->wallet->id,
                'balance'  => (float) $this->wallet->balance,
                'customer' => $this->wallet->customer ? [
                    'id'   => $this->wallet->customer->id,
                    'name' => $this->wallet->customer->user->name ?? null,
                ] : null,
            ]),
            'created_at'     => $this->created_at,
        ];
    }
}
