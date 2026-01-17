<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DarbAssabilShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_id'          => $this->order_id,
            'darb_reference'    => $this->darb_reference,
            'darb_id'           => $this->darb_id,
            'status'            => $this->status,
            'amount'            => (float) $this->amount,
            'currency'          => $this->currency,
            'recipient_name'    => $this->recipient_name,
            'recipient_phone'   => $this->recipient_phone,
            'recipient_city'    => $this->recipient_city,
            'recipient_area'    => $this->recipient_area,
            'recipient_address' => $this->recipient_address,
            'error_message'     => $this->error_message,
            'synced_at'         => $this->synced_at,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
