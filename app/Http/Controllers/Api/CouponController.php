<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Http\Traits\ApiResponse;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CouponService $service)
    {}

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $filters = $request->only(['search', 'is_active']);

        $coupons = $this->service->getAll($filters, $perPage);

        return $this->success(CouponResource::collection($coupons), 'Coupons fetched successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|unique:coupons,code',
            'value'       => 'required|numeric|min:0',
            'type'        => 'required|in:fixed,percent',
            'expiry_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        try {
            $coupon = $this->service->create($validated);
            return $this->success(new CouponResource($coupon), 'Coupon created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(Coupon $coupon)
    {
        return $this->success(new CouponResource($coupon), 'Coupon fetched successfully');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code'        => 'sometimes|unique:coupons,code,' . $coupon->id,
            'value'       => 'sometimes|numeric|min:0',
            'type'        => 'sometimes|in:fixed,percent',
            'expiry_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active'   => 'sometimes|boolean',
        ]);

        try {
            $coupon = $this->service->update($coupon, $validated);
            return $this->success(new CouponResource($coupon), 'Coupon updated successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(Coupon $coupon)
    {
        try {
            $this->service->delete($coupon);
            return $this->success(null, 'Coupon deleted successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code'   => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $coupon = $this->service->validateCoupon($request->code, $request->amount);

            if (! $coupon) {
                return $this->error('Invalid coupon', 422);
            }

            $discount = $this->service->calculateDiscount($coupon, $request->amount);

            return $this->success([
                'coupon'          => new CouponResource($coupon),
                'discount_amount' => $discount,
                'new_total'       => $request->amount - $discount,
            ], 'Coupon verified successfully');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
