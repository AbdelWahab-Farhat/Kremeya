<?php
namespace App\Services;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Coupon::query()
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where('code', 'like', '%' . $filters['search'] . '%');
            })
            ->when(isset($filters['is_active']), function (Builder $query) use ($filters) {
                $query->where('is_active', $filters['is_active']);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);
        return $coupon->fresh();
    }

    public function delete(Coupon $coupon): bool
    {
        return $coupon->delete();
    }

    public function validateCoupon(?string $code, float $orderTotal): ?Coupon
    {
        if (empty($code)) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValid()) {
            throw ValidationException::withMessages([
                'coupon_code' => ['الكوبون غير صالح أو منتهي الصلاحية.'],
            ]);
        }

        return $coupon;
    }

    public function calculateDiscount(Coupon $coupon, float $total): float
    {
        if ($coupon->type === 'fixed') {
            return min($coupon->value, $total);
        }

        if ($coupon->type === 'percent') {
            return $total * ($coupon->value / 100);
        }

        return 0;
    }
}
