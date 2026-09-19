<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Hr\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Company announcements, circulars and notices for the signed-in staff member.
 */
class SalesmanAnnouncementController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $announcements = Announcement::query()
            ->visibleTo($user->role)
            ->latest('published_at')
            ->paginate(min((int) $request->integer('per_page', 20), 50));

        return $this->success([
            'announcements' => $announcements->items(),
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page' => $announcements->lastPage(),
                'total' => $announcements->total(),
            ],
        ]);
    }
}
