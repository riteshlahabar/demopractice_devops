<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminDashboardController extends AdminApiController
{
    public function dashboard(Request $request): JsonResponse
    {
        $this->admin($request);

        return $this->success([
            'salesmen' => User::query()->where('role', User::ROLE_SALESMAN)->count(),
            'dealers' => User::query()->where('role', User::ROLE_DEALER)->count(),
            'pending_dealers' => User::query()->where('role', User::ROLE_DEALER)->where('status', 'pending_approval')->count(),
            'customers' => User::query()->where('role', User::ROLE_CUSTOMER)->count(),
            'admin_review_orders' => Order::query()->where('status', 'admin_review')->count(),
        ]);
    }
}
