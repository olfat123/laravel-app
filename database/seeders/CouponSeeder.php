<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {

        $coupons = [
            [
                'code'             => 'WELCOME10',
                'type'             => 'percentage',
                'value'            => 10,
                'min_order_amount' => 0,
                'max_uses'         => null,
                'used_count'       => 0,
                'expires_at'       => null,
                'is_active'        => true,
            ],
            [
                'code'             => 'SAVE20',
                'type'             => 'percentage',
                'value'            => 20,
                'min_order_amount' => 100,
                'max_uses'         => 500,
                'used_count'       => 0,
                'expires_at'       => now()->addYear(),
                'is_active'        => true,
            ],
            [
                'code'             => 'FLAT15',
                'type'             => 'fixed',
                'value'            => 15,
                'min_order_amount' => 50,
                'max_uses'         => 200,
                'used_count'       => 0,
                'expires_at'       => now()->addMonths(6),
                'is_active'        => true,
            ],
            [
                'code'             => 'KIDS15',
                'type'             => 'percentage',
                'value'            => 15,
                'min_order_amount' => 30,
                'max_uses'         => null,
                'used_count'       => 0,
                'expires_at'       => null,
                'is_active'        => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
