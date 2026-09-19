<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Api\ApiController;
use App\Models\Communication\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The notification inbox, shared by all three mobile apps.
 *
 * Every query is scoped to the authenticated user's own rows, so the same
 * controller can serve customer, dealer and salesman routes without any of
 * them being able to read another account's notifications.
 */
class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        return $this->success([
            'notifications' => $notifications->items(),
            'unread_count' => Notification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Just the bell badge number, so the app can refresh it cheaply.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        return $this->success([
            'unread_count' => Notification::query()->where('user_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer'],
        ]);

        $query = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at');

        // No ids means "mark the whole inbox read", which is what the app's
        // bell icon does; the ownership filter above still applies either way.
        if (! empty($validated['ids'])) {
            $query->whereIn('id', $validated['ids']);
        }

        $updated = $query->update(['read_at' => now()]);

        return $this->success(['updated' => $updated], 'Notifications marked read.');
    }
}
