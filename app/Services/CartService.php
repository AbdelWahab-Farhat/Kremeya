<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function addToCart(Customer $customer, array $payload): Cart
    {
        $items = $this->normalizePayload($payload); // [productId => qty]

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['No valid items provided.'],
            ]);
        }


        $productIds = array_keys($items);

        $existingIds = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->all();

        $missing = array_values(array_diff($productIds, $existingIds));
        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'product_id' => ['Invalid product ids: ' . implode(', ', $missing)],
            ]);
        }

        return DB::transaction(function () use ($customer, $items, $productIds) {

            $cart = $customer->cart()->firstOrCreate([]);

            $existingPivotQty = DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->whereIn('product_id', $productIds)
                ->pluck('quantity', 'product_id')
                ->toArray();

            $toAttach = [];

            foreach ($items as $productId => $qtyToAdd) {
                if (isset($existingPivotQty[$productId])) {
                    $newQty = (int)$existingPivotQty[$productId] + (int)$qtyToAdd;

                    $cart->products()->updateExistingPivot($productId, [
                        'quantity' => $newQty,
                    ]);
                } else {
                    $toAttach[$productId] = ['quantity' => (int)$qtyToAdd];
                }
            }

            if (!empty($toAttach)) {
                $cart->products()->attach($toAttach);
            }

            return $cart->load('products.images');
        });
    }

    public function removeFromCart(Customer $customer, array $payload): Cart
    {
        $items = $this->normalizePayload($payload); // [productId => qtyToRemove]

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['No valid items provided.'],
            ]);
        }

        return DB::transaction(function () use ($customer, $items) {

            $cart = $customer->cart()->first();

            if (!$cart) {
                throw ValidationException::withMessages([
                    'cart' => ['Cart not found for this customer.'],
                ]);
            }

            $productIds = array_keys($items);

            $existingPivotQty = DB::table('cart_items')
                ->where('cart_id', $cart->id)
                ->whereIn('product_id', $productIds)
                ->pluck('quantity', 'product_id')
                ->toArray();

            foreach ($items as $productId => $qtyToRemove) {
                if (!isset($existingPivotQty[$productId])) {
                    continue;
                }

                $remaining = (int)$existingPivotQty[$productId] - (int)$qtyToRemove;

                if ($remaining > 0) {
                    $cart->products()->updateExistingPivot($productId, [
                        'quantity' => $remaining,
                    ]);
                } else {
                    $cart->products()->detach($productId);
                }
            }

           return $cart->load('products.images');
        });
    }

    /**
     * Accepts:
     * - { product_id, quantity }
     * - { items: { product_id, quantity } }
     * - { items: [ {product_id, quantity}, ... ] }
     * - { items: { "10": 20, "15": 2 } }
     * Returns: [productId => qty]
     */
    private function normalizePayload(array $payload): array
    {
        // if root is single item
        if (isset($payload['product_id'])) {
            $pid = (int) $payload['product_id'];
            $qty = (int) ($payload['quantity'] ?? 1);
            return ($pid > 0 && $qty > 0) ? [$pid => $qty] : [];
        }

        // if payload has items
        if (array_key_exists('items', $payload)) {
            $data = $payload['items'];

            // items is single object
            if (is_array($data) && isset($data['product_id'])) {
                $pid = (int) $data['product_id'];
                $qty = (int) ($data['quantity'] ?? 1);
                return ($pid > 0 && $qty > 0) ? [$pid => $qty] : [];
            }

            // items is array/map
            if (is_array($data)) {
                return $this->normalizeItems($data);
            }

            return [];
        }

        return [];
    }

    /**
     * Normalize input to: [productId => qty]
     * Supports:
     * - [10 => 2, 15 => 1]
     * - [['product_id'=>10,'quantity'=>2], ...]
     * - [10, 15] (qty=1)
     */
    private function normalizeItems(array $data): array
    {
        $items = [];

        $isAssoc = array_keys($data) !== range(0, count($data) - 1);

        // Associative map: ["10"=>20, "15"=>2]
        if ($isAssoc) {
            foreach ($data as $key => $value) {
                // keys should be product ids
                if (is_string($key) && ctype_digit($key)) {
                    $productId = (int)$key;
                    $qty = (int)$value;

                    if ($productId > 0 && $qty > 0) {
                        $items[$productId] = ($items[$productId] ?? 0) + $qty;
                    }
                }
            }
            return $items;
        }

        // List:
        foreach ($data as $row) {
            // Case: [10, 15]
            if (is_int($row) || (is_string($row) && ctype_digit($row))) {
                $productId = (int)$row;
                if ($productId > 0) {
                    $items[$productId] = ($items[$productId] ?? 0) + 1;
                }
                continue;
            }

            // Case: [['product_id'=>10,'quantity'=>2], ...]
            if (!is_array($row)) continue;

            $productId = isset($row['product_id']) ? (int)$row['product_id'] : 0;
            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;

            if ($productId > 0 && $qty > 0) {
                $items[$productId] = ($items[$productId] ?? 0) + $qty;
            }
        }

        return $items;
    }
}
