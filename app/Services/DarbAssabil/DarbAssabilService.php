<?php
namespace App\Services\DarbAssabil;

use App\Models\DarbAssabilShipment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DarbAssabilService
{
    public function __construct(
        protected DarbAssabilClient $client
    ) {
    }

    /**
     * Create a local shipment in Darb Assabil for an order.
     */
    public function createShipment(Order $order, array $locationData = []): DarbAssabilShipment
    {
        $order->load(['customer.user', 'region', 'city', 'darbAssabilShipment']);

        // Delete failed shipment if exists (allow retry)
        if ($order->darbAssabilShipment && $order->darbAssabilShipment->status === DarbAssabilShipment::STATUS_FAILED) {
            $order->darbAssabilShipment->delete();
        }

        // Check if already has successful shipment
        if ($order->darbAssabilShipment && $order->darbAssabilShipment->status !== DarbAssabilShipment::STATUS_FAILED) {
            throw new \Exception('الطلب لديه شحنة مسجلة مسبقاً');
        }

        // Get recipient data
        $recipientData = $this->getRecipientData($order, $locationData);

        // Build API payload
        $payload = $this->buildPayload($order, $recipientData);

        // Create local shipment record
        $shipment = DarbAssabilShipment::create([
            'order_id'          => $order->id,
            'status'            => DarbAssabilShipment::STATUS_PENDING,
            'amount'            => $order->total,
            'currency'          => config('darb_assabil.currency', 'lyd'),
            'recipient_name'    => $recipientData['name'],
            'recipient_phone'   => $recipientData['phone'],
            'recipient_city'    => $recipientData['city'],
            'recipient_area'    => $recipientData['area'],
            'recipient_address' => $recipientData['address'],
            'api_request'       => $payload,
            'created_by'        => Auth::id(),
        ]);

        // Call Darb Assabil API
        $response = $this->client->post('/api/local/shipments', $payload);

        if ($response['success']) {
            $shipment->update([
                'darb_reference' => $response['data']['reference'] ?? null,
                'darb_id'        => $response['data']['_id'] ?? null,
                'status'         => $response['data']['status'] ?? DarbAssabilShipment::STATUS_CREATED,
                'api_response'   => $response['data'],
                'synced_at'      => now(),
            ]);
        } else {
            $shipment->update([
                'status'        => DarbAssabilShipment::STATUS_FAILED,
                'error_message' => $response['error'] ?? 'Unknown error',
                'api_response'  => $response,
            ]);
        }

        return $shipment->fresh();
    }

    /**
     * Get shipment from Darb Assabil by reference ID.
     */
    public function getShipment(string $shipmentId): array
    {
        $response = $this->client->get("/api/local/shipments/{$shipmentId}");

        if ($response['success']) {
            return [
                'success' => true,
                'data'    => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error'   => $response['error'] ?? 'فشل في جلب الشحنة',
        ];
    }

    /**
     * Get all shipments from Darb Assabil.
     */
    public function getAllShipments(array $query = []): array
    {
        $response = $this->client->get('/api/local/shipments', $query);

        if ($response['success']) {
            return [
                'success' => true,
                'data'    => $response['data'],
            ];
        }

        return [
            'success' => false,
            'error'   => $response['error'] ?? 'فشل في جلب الشحنات',
            'data'    => [],
        ];
    }

    /**
     * Sync shipment status from Darb Assabil.
     */
    public function syncStatus(DarbAssabilShipment $shipment): bool
    {
        // Use darb_id (MongoDB ObjectId) for API calls
        $shipmentIdentifier = $shipment->darb_id ?? $shipment->darb_reference;

        if (empty($shipmentIdentifier)) {
            return false;
        }

        $response = $this->client->get("/api/local/shipments/{$shipmentIdentifier}");

        if ($response['success'] && isset($response['data'])) {
            $shipment->update([
                'status'       => $response['data']['status'] ?? $shipment->status,
                'api_response' => $response['data'],
                'synced_at'    => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Get recipient data from order.
     */
    protected function getRecipientData(Order $order, array $locationData = []): array
    {
        return [
            'name' => $order->customer?->user?->name ?? "طلب #{$order->id}",
            'phone'   => $order->customer?->user?->phone ?? '',
            'city'    => $locationData['city'] ?? $order->city?->name ?? '',
            'area'    => $locationData['area'] ?? $order->region?->name ?? '',
            'address' => $locationData['address'] ?? '',
        ];
    }

    /**
     * Build the API payload for creating a shipment (required fields only).
     */
    protected function buildPayload(Order $order, array $recipientData): array
    {
        $serviceId      = config('darb_assabil.service_id');
        $defaultContact = config('darb_assabil.default_contact');

        return [
            'service'  => $serviceId,
            'contacts' => [$defaultContact],
            'products' => [
                [
                    'title' => "طلب #{$order->order_code}",
                    'quantity'     => 1,
                    'amount'       => (float) $order->total,
                    'currency'     => config('darb_assabil.currency', 'lyd'),
                    'isChargeable' => true,
                ],
            ],
            'paymentBy' => config('darb_assabil.payment_by', 'receiver'),
            'to'        => [
                'countryCode' => config('darb_assabil.country_code', 'lby'),
                'city'        => $recipientData['city'],
                'area'        => $recipientData['area'],
                'address'     => $recipientData['address'] ?? '',
                // 'phone' field removed as it causes API error
            ],
            'metadata'  => [
                'kremeya_order_id'   => (string) $order->id,
                'kremeya_order_code' => (string) $order->order_code,
                'recipient_name'     => (string) ($recipientData['name'] ?? ''),
                'recipient_phone'    => (string) ($recipientData['phone'] ?? ''),
            ],
        ];
    }

    /**
     * Check if the service is properly configured and enabled.
     */
    public function isEnabled(): bool
    {
        return config('darb_assabil.enabled', false) && $this->client->isConfigured();
    }

    /**
     * Cancel a shipment in Darb Assabil.
     */
    public function cancelShipment(DarbAssabilShipment $shipment): bool
    {
        // Use darb_id (MongoDB ObjectId) for API calls
        $shipmentIdentifier = $shipment->darb_id ?? $shipment->darb_reference;

        if (empty($shipmentIdentifier)) {
            return false;
        }

        $response = $this->client->delete("/api/local/shipments/{$shipmentIdentifier}");

        if ($response['success']) {
            $shipment->update([
                'status'       => DarbAssabilShipment::STATUS_CANCELLED,
                'api_response' => $response['data'] ?? $response,
                'synced_at'    => now(),
            ]);
            return true;
        }

        Log::warning('Failed to cancel Darb Assabil shipment', [
            'shipment_id'    => $shipment->id,
            'darb_id'        => $shipment->darb_id,
            'darb_reference' => $shipment->darb_reference,
            'response'       => $response,
        ]);

        return false;
    }
}
