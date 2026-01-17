<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDarbAssabilShipmentRequest;
use App\Http\Resources\DarbAssabilShipmentResource;
use App\Models\DarbAssabilShipment;
use App\Models\Order;
use App\Services\DarbAssabil\DarbAssabilService;

class DarbAssabilController extends Controller
{
    public function __construct(
        protected DarbAssabilService $darbService
    ) {
    }

    /**
     * Create a shipment in Darb Assabil for an order.
     */
    public function createShipment(CreateDarbAssabilShipmentRequest $request, Order $order)
    {
        // Check if service is enabled
        if (! $this->darbService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'خدمة درب السبيل غير مفعلة',
            ], 503);
        }

        try {
            $locationData = [
                'city'    => $request->city,
                'area'    => $request->area,
                'address' => $request->address,
            ];

            $shipment = $this->darbService->createShipment($order, $locationData);

            return response()->json([
                'success' => $shipment->isSuccessful(),
                'message' => $shipment->isSuccessful()
                    ? 'تم إنشاء الشحنة بنجاح'
                    : 'فشل في إنشاء الشحنة: ' . $shipment->error_message,
                'data'    => new DarbAssabilShipmentResource($shipment),
            ], $shipment->isSuccessful() ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all shipments from Darb Assabil.
     */
    public function index()
    {
        if (! $this->darbService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'خدمة درب السبيل غير مفعلة',
            ], 503);
        }

        $response = $this->darbService->getAllShipments();

        return response()->json([
            'success' => $response['success'],
            'data'    => $response['data'] ?? [],
            'message' => $response['error'] ?? null,
        ]);
    }

    /**
     * Get local shipment record.
     */
    public function showLocalShipment(DarbAssabilShipment $shipment)
    {
        return response()->json([
            'success' => true,
            'data'    => new DarbAssabilShipmentResource($shipment),
        ]);
    }

    /**
     * Sync shipment status from Darb Assabil.
     */
    public function syncStatus(DarbAssabilShipment $shipment)
    {
        if (! $this->darbService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'خدمة درب السبيل غير مفعلة',
            ], 503);
        }

        $synced = $this->darbService->syncStatus($shipment);

        return response()->json([
            'success' => $synced,
            'message' => $synced ? 'تم مزامنة الحالة' : 'فشل في المزامنة',
            'data'    => new DarbAssabilShipmentResource($shipment->fresh()),
        ]);
    }

    /**
     * Cancel a shipment.
     */
    public function cancelShipment(DarbAssabilShipment $shipment)
    {
        if (! $this->darbService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'خدمة درب السبيل غير مفعلة',
            ], 503);
        }

        $cancelled = $this->darbService->cancelShipment($shipment);

        return response()->json([
            'success' => $cancelled,
            'message' => $cancelled ? 'تم إلغاء الشحنة' : 'فشل في إلغاء الشحنة',
            'data'    => new DarbAssabilShipmentResource($shipment->fresh()),
        ]);
    }
}
