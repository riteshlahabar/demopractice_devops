<?php

namespace App\Http\Controllers\Api\Dealer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Purchase reporting for the signed-in dealer.
 *
 * Both actions read only this dealer's own orders, so the report can never
 * expose another dealer's volumes or pricing.
 */
class DealerReportController extends ApiController
{
    /** Widest window a dealer may request, to keep the aggregate cheap. */
    private const MAX_MONTHS = 24;

    public function orders(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $since = $this->windowStart($request);

        $orders = Order::query()
            ->where('dealer_id', $user->id)
            ->where('created_at', '>=', $since)
            ->get(['id', 'status', 'grand_total', 'created_at']);

        return $this->success([
            'from' => $since->toDateString(),
            'totals' => [
                'orders' => $orders->count(),
                'value' => round((float) $orders->sum('grand_total'), 2),
            ],
            'by_status' => $orders->groupBy('status')->map(fn ($group): array => [
                'count' => $group->count(),
                'value' => round((float) $group->sum('grand_total'), 2),
            ]),
            'by_month' => $orders
                ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m'))
                ->map(fn ($group): array => [
                    'count' => $group->count(),
                    'value' => round((float) $group->sum('grand_total'), 2),
                ])
                ->sortKeys(),
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_DEALER);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $since = $this->windowStart($request);

        $rows = OrderItem::query()
            ->whereIn('order_id', Order::query()
                ->where('dealer_id', $user->id)
                ->where('created_at', '>=', $since)
                ->select('id'))
            ->with('product:id,name,sku')
            ->get();

        $products = $rows
            ->groupBy('product_id')
            ->map(fn ($group): array => [
                'product' => $group->first()->product?->name ?? 'Product',
                'sku' => $group->first()->product?->sku ?? '',
                'quantity' => round((float) $group->sum('quantity'), 3),
                'value' => round((float) $group->sum('line_total'), 2),
            ])
            ->sortByDesc('value')
            ->values()
            // Top movers only; the full line-item history is the order list's job.
            ->take(25);

        return $this->success([
            'from' => $since->toDateString(),
            'products' => $products,
            'total_value' => round((float) $rows->sum('line_total'), 2),
        ]);
    }

    private function windowStart(Request $request): Carbon
    {
        $months = min(max((int) $request->integer('months', 6), 1), self::MAX_MONTHS);

        return now()->subMonthsNoOverflow($months)->startOfDay();
    }
}
