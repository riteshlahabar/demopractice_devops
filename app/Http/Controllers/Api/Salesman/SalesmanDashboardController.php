<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\DealerProfile;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanDashboardController extends SalesmanApiController
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        return $this->success([
            'assigned_dealers' => DealerProfile::query()->where('salesman_id', $user->id)->count(),
            'pending_orders' => Order::query()->where('salesman_id', $user->id)->where('status', 'salesman_review')->count(),
            'today_collections' => Payment::query()->where('collected_by', $user->id)->whereDate('created_at', today())->sum('amount'),
            'profile' => $user->load('salesmanProfile'),
        ]);
    }

    public function dealers(Request $request): JsonResponse
    {
        $dealers = DealerProfile::query()
            ->with('user.addresses')
            ->where('salesman_id', $this->salesman($request)->id)
            ->paginate($request->integer('per_page', 20));

        return $this->success(['dealers' => $dealers]);
    }
}
