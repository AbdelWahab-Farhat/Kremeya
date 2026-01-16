<?php
namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Http\Traits\ApiResponse;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * GET /v1/customers/{customer}/cart
     */
    public function show(Customer $customer)
    {
        $cart = $customer->cart()
            ->with('products.images')
            ->firstOrCreate([]);

        return $this->success(new CartResource($cart), 'Cart fetched successfully');
    }

    /**
     * POST /v1/customers/{customer}/cart/items
     * Body: { "items": ... }
     */
    public function addItems(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
        ]);

        $cart = $this->cartService->addToCart($customer, $validated['items']);

        return $this->success(new CartResource($cart), 'Items added to cart successfully');
    }

    /**
     * DELETE /v1/customers/{customer}/cart/items
     * Body: { "items": ... }
     */
    public function removeItems(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
        ]);

        $cart = $this->cartService->removeFromCart($customer, $validated['items']);

        return $this->success(new CartResource($cart), 'Items removed from cart successfully');
    }

    /**
     * DELETE /v1/customers/{customer}/cart
     * Clears cart (detach all products)
     */
    public function clear(Customer $customer)
    {
        $cart = $customer->cart()->first();

        if ($cart) {
            $cart->products()->detach();
            $cart->load('products.images');
        } else {
            $cart = $customer->cart()->firstOrCreate([])->load('products.images');
        }

        return $this->success(new CartResource($cart), 'Cart cleared successfully');
    }
}
