<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Order::query()
            ->with(['customer', 'products'])
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where('order_code', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('customer', function ($q) use ($filters) {
                        // Assuming Customer has user with name, or customer itself has name. Check Customer model.
                        // Based on previous Customer model view, it belongsTo User. Let's assume we search User name.
                        $q->whereHas('user', function ($u) use ($filters) {
                            $u->where('name', 'like', '%' . $filters['search'] . '%');
                        });
                    });
            })
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['customer_id']), function (Builder $query) use ($filters) {
                $query->where('customer_id', $filters['customer_id']);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createFromCart(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::findOrFail($data['customer_id']);
            $cart     = $customer->cart;

            if (! $cart || $cart->products()->count() === 0) {
                throw new Exception('Cart is empty');
            }

            // Coupon Logic
            $couponId       = null;
            $discountAmount = 0;

            if (! empty($data['coupon_code'])) {
                $cartTotal = $cart->total ?? $cart->products->sum(fn($p) => $p->selling_price * $p->pivot->quantity);
                $coupon    = $this->couponService->validateCoupon($data['coupon_code'], $cartTotal);

                if ($coupon) {
                    $couponId       = $coupon->id;
                    $discountAmount = $this->couponService->calculateDiscount($coupon, $cartTotal);
                    $coupon->increment('used_count');
                }
            }

            $order = Order::create([
                'customer_id'     => $data['customer_id'],
                'status'          => $data['status'] ?? 'new',
                'notes'           => $data['notes'] ?? null,
                'region_id'       => $data['region_id'] ?? $customer->region_id,
                'city_id'         => $data['city_id'] ?? $customer->city_id,
                'coupon_id'       => $couponId,
                'discount_amount' => $discountAmount,
            ]);

            // Sync products from cart
            $syncData = [];
            foreach ($cart->products as $product) {
                $syncData[$product->id] = [
                    'quantity'   => $product->pivot->quantity,
                    'unit_price' => $product->selling_price,
                ];
            }
            $order->products()->sync($syncData);

            // Clear Cart
            $cart->products()->detach();
            // Or $cart->delete(); depending on logic. Usually just clearing items.

            return $order->load(['customer', 'products']);
        });
    }

    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $order->update($data);

            if (isset($data['products'])) {
                $this->syncProducts($order, $data['products']);
            }

            return $order->load(['customer', 'products']);
        });
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    public function restore($id): ?Order
    {
        $order = Order::withTrashed()->find($id);

        if ($order && $order->trashed()) {
            $order->restore();
            return $order;
        }

        return null;
    }

    private function syncProducts(Order $order, array $products): void
    {
        $syncData = [];
        foreach ($products as $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $syncData[$item['id']] = [
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product->selling_price, // Snapshot price
                ];
            }
        }
        $order->products()->sync($syncData);
    }
}
