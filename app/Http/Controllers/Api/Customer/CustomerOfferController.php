<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Engagement\Coupon;
use App\Services\Engagement\CouponValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Live offers and server-side coupon validation for the checkout screen.
 */
class CustomerOfferController extends ApiController
{
    public function __construct(private readonly CouponValidationService $coupons) {}

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $offers = Coupon::query()
            ->availableTo($user->role)
            // Deliberately omits usage counters and internal limits: the app
            // only needs enough to render the offer card.
            ->get(['id', 'code', 'title', 'description', 'discount_type', 'discount_value', 'max_discount', 'min_order_value', 'ends_at']);

        return $this->success(['offers' => $offers]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'order_value' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $result = $this->coupons->validate(
                $user,
                $validated['code'],
                (float) $validated['order_value']
            );
        } catch (RuntimeException $exception) {
            return $this->fail($exception->getMessage(), 422);
        }

        return $this->success([
            'code' => $result['coupon']->code,
            'title' => $result['coupon']->title,
            'discount' => $result['discount'],
            'payable' => round((float) $validated['order_value'] - $result['discount'], 2),
        ], 'Coupon applied.');
    }
}
