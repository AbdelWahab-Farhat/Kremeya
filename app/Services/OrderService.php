<?php
namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected CouponService $couponService;
    protected WalletService $walletService;

    public function __construct(CouponService $couponService, WalletService $walletService)
    {
        $this->couponService = $couponService;
        $this->walletService = $walletService;
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Order::query()
            ->with(['customer', 'products', 'darbAssabilShipment'])
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where('order_code', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('customer', function ($q) use ($filters) {
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

            // Calculate cart total
            $cartTotal = $cart->products->sum(fn($p) => $p->selling_price * $p->pivot->quantity);

            // Coupon
            $couponId       = null;
            $discountAmount = 0;

            if (! empty($data['coupon_code'])) {
                $coupon = $this->couponService->validateCoupon($data['coupon_code'], $cartTotal);

                if ($coupon) {
                    $couponId       = $coupon->id;
                    $discountAmount = $this->couponService->calculateDiscount($coupon, $cartTotal);
                    $coupon->increment('used_count');
                }
            }

            // Final order total after discount
            $finalTotal = $cartTotal - $discountAmount;

            // Payment method validation
            $paymentMethod = $data['payment_method'] ?? PaymentMethod::CASH->value;

            if ($paymentMethod === PaymentMethod::WALLET->value || $paymentMethod === PaymentMethod::WALLET) {
                $wallet = $this->walletService->getOrCreateWallet($customer->id);

                if (! $wallet->hasEnoughBalance($finalTotal)) {
                    throw new Exception("رصيد المحفظة غير كافي. الرصيد الحالي: {$wallet->balance}, المطلوب: {$finalTotal}", 400);
                }
            }

            $order = Order::create([
                'customer_id'     => $data['customer_id'],
                'status'          => $data['status'] ?? 'new',
                'payment_method'  => $paymentMethod,
                'notes'           => $data['notes'] ?? null,
                'region_id'       => $data['region_id'] ?? $customer->region_id,
                'city_id'         => $data['city_id'] ?? $customer->city_id,
                'coupon_id'       => $couponId,
                'discount_amount' => $discountAmount,
            ]);

            // Validate stock availability and prepare sync data
            $syncData = [];
            foreach ($cart->products as $product) {
                $quantity = $product->pivot->quantity;

                if ($product->stock < $quantity) {
                    throw new Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$quantity}", code: 400);
                }

                $syncData[$product->id] = [
                    'quantity'   => $quantity,
                    'unit_price' => $product->selling_price,
                ];
            }

            // Sync products to order
            $order->products()->sync($syncData);

            // Reduce stock for each product
            foreach ($cart->products as $product) {
                $product->decrement('stock', $product->pivot->quantity);
            }

            // Deduct from wallet if payment method is wallet
            if ($paymentMethod === PaymentMethod::WALLET->value || $paymentMethod === PaymentMethod::WALLET) {
                $wallet = $this->walletService->getOrCreateWallet($customer->id);
                $this->walletService->withdraw($wallet, $finalTotal, "دفع طلب رقم: {$order->order_code}", $order);
            }

            // Clear Cart
            $cart->products()->detach();

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
