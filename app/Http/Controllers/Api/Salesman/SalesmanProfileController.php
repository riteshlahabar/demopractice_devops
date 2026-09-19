<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Communication\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Own profile and support-ticket creation for the signed-in salesman.
 */
final class SalesmanProfileController extends SalesmanApiController
{
    public function profile(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        return $this->success(['user' => $user->load('salesmanProfile')]);
    }

    public function support(Request $request): JsonResponse
    {
        $user = $this->salesman($request);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::query()->create([
            'user_id' => $user->id,
            'ticket_no' => 'TKT'.now()->format('ymdHis').random_int(100, 999),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return $this->success(['ticket' => $ticket], 'Support ticket created.', 201);
    }
}
