<?php

namespace App\Services\Engagement;

use App\Models\Engagement\Coupon;
use App\Models\Engagement\CouponRedemption;
use App\Models\User;
use RuntimeException;

/**
 * Decides whether a coupon may be applied, and for how much.
 *
 * All of it runs server side. The app sends a code and an order value and gets
 * back a discount; it never computes the discount itself, because a client
 * that can pick its own discount is a client that will.
 */
class CouponValidationService
{
    /**
     * @return array{coupon: Coupon, discount: float}
     *
     * @throws RuntimeException when the coupon cannot be used.
     */
    public function validate(User $user, string $code, float $orderValue): array
    {
        $coupon = Coupon::query()
            ->availableTo($user->role)
            ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])
            ->first();

        if ($coupon === null) {
            throw new RuntimeException('This coupon code is not valid.');
        }

        if ($orderValue < (float) $coupon->min_order_value) {
            throw new RuntimeException(
                'A minimum order of '.number_format((float) $coupon->min_order_value, 2).' is required.'
            );
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new RuntimeException('This coupon has been fully redeemed.');
        }

        $usedByUser = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->count();

        if ($usedByUser >= $coupon->usage_limit_per_user) {
            throw new RuntimeException('You have already used this coupon.');
        }

        $discount = $coupon->discountFor($orderValue);

        if ($discount <= 0) {
            throw new RuntimeException('This coupon gives no discount on your cart.');
        }

        return ['coupon' => $coupon, 'discount' => $discount];
    }
}
