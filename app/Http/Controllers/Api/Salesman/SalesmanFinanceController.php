<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Field\Expense;
use App\Models\Field\SalarySlip;
use App\Models\Field\SalesmanTarget;
use App\Models\Finance\Payment;
use App\Models\Sales\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalesmanFinanceController extends SalesmanApiController
{
    public function collectPayment(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'dealer_id' => ['required', 'integer', 'exists:users,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'payment_mode' => ['required', 'string', 'max:40'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
        ]);

        $dealer = User::query()->where('role', User::ROLE_DEALER)->findOrFail($validated['dealer_id']);

        if ((int) $dealer->dealerProfile?->salesman_id !== (int) $user->id) {
            return $this->fail('Dealer is not assigned to this salesman.', 403);
        }

        if (! empty($validated['order_id'])) {
            $order = Order::query()->findOrFail($validated['order_id']);

            if ((int) $order->dealer_id !== (int) $dealer->id || (int) $order->salesman_id !== (int) $user->id) {
                return $this->fail('Selected order does not belong to this dealer or salesman.', 403);
            }
        }

        $payment = Payment::query()->create([
            'payment_no' => 'PAY'.now()->format('ymdHis').random_int(100, 999),
            'order_id' => $validated['order_id'] ?? null,
            'payer_id' => $dealer->id,
            'collected_by' => $user->id,
            'payment_mode' => $validated['payment_mode'],
            'status' => 'collected',
            'amount' => $validated['amount'],
            'transaction_ref' => $validated['transaction_ref'] ?? null,
            'paid_at' => now(),
        ]);

        return $this->success(['payment' => $payment], 'Payment collected.', 201);
    }

    public function expenses(Request $request): JsonResponse
    {
        $expenses = Expense::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(20);

        return $this->success(['expenses' => $expenses]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'expense_type' => ['required', 'string', 'max:40'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'receipt_path' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $expense = Expense::query()->create($validated + ['salesman_id' => $user->id]);

        return $this->success(['expense' => $expense], 'Expense submitted.', 201);
    }

    public function salary(Request $request): JsonResponse
    {
        $slips = SalarySlip::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(12);

        return $this->success(['salary_slips' => $slips]);
    }

    public function targets(Request $request): JsonResponse
    {
        $targets = SalesmanTarget::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->latest()
            ->paginate(12);

        return $this->success(['targets' => $targets]);
    }
}
