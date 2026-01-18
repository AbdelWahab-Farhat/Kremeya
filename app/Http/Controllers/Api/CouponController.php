<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
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

        return $this->paginatedSuccess($coupons, CouponResource::class, 'Coupons fetched successfully');
    }

    public function store(CreateCouponRequest $request)
    {
        try {
            $coupon = $this->service->create($request->validated());
            return $this->success(new CouponResource($coupon), 'Coupon created successfully', 201);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(Coupon $coupon)
    {
        return $this->success(new CouponResource($coupon), 'Coupon fetched successfully');
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        try {
            $coupon = $this->service->update($coupon, $request->validated());
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
