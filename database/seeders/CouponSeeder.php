<?php
namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code'        => 'WELCOME10',
                'type'        => 'percent',
                'value'       => 10,
                'expiry_date' => now()->addMonths(3),
                'usage_limit' => 100,
                'used_count'  => 0,
                'is_active'   => true,
            ],
            [
                'code'        => 'SAVE20',
                'type'        => 'percent',
                'value'       => 20,
                'expiry_date' => now()->addMonth(),
                'usage_limit' => 50,
                'used_count'  => 0,
                'is_active'   => true,
            ],
            [
                'code'        => 'FLAT50',
                'type'        => 'fixed',
                'value'       => 50,
                'expiry_date' => now()->addMonths(2),
                'usage_limit' => 200,
                'used_count'  => 0,
                'is_active'   => true,
            ],
            [
                'code'        => 'VIP25',
                'type'        => 'percent',
                'value'       => 25,
                'expiry_date' => now()->addYear(),
                'usage_limit' => null, // Unlimited
                'used_count'  => 0,
                'is_active'   => true,
            ],
            [
                'code'        => 'EXPIRED',
                'type'        => 'percent',
                'value'       => 15,
                'expiry_date' => now()->subDay(), // Already expired
                'usage_limit' => 100,
                'used_count'  => 0,
                'is_active'   => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
