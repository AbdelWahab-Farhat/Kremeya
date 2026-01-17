<?php
namespace App\Http\Controllers;

use App\Enums\UserRoles;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\LogResource;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ApiResponse;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OrderService $service)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = $request->only(['search', 'status', 'customer_id']);

        $resource = $this->service->getAll($filters, $perPage);

        return $this->success(OrderResource::collection($resource));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->service->createFromCart($request->validated());
            return $this->success(new OrderResource($order), 'Order created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 400); // 400 mostly for empty cart
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return $this->success(new OrderResource($order->load(['customer', 'products', 'darbAssabilShipment'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order = $this->service->update($order, $request->validated());
        return $this->success(new OrderResource($order), 'Order updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        if (! $user->hasRole(UserRoles::ADMIN->value)) {
            return $this->error('Only Access For Admin', 403);
        }
        $this->service->delete($order);
        return $this->success(null, 'Order deleted successfully');
    }

    public function restore($id)
    {
        $order = $this->service->restore($id);
        if (! $order) {
            return $this->error('Order not found or not deleted', 404);
        }

        return $this->success(new OrderResource($order), 'Order restored successfully');
    }

    public function logs(Order $order)
    {
        return $this->success(LogResource::collection($order->logs));
    }
}
