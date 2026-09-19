<?php

namespace App\Http\Middleware;

use App\Contracts\Admin\Access\AdminAccessContract;
use App\Contracts\Admin\Access\AdminRouteRequirementContract;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after `admin`: checks the page / action against the user's role.
 * The dashboard is the login landing page, so a role without it is sent to
 * the first section it can open instead of seeing "no permission".
 */
class EnsureAdminPermission
{
    public function __construct(
        private readonly AdminAccessContract $access,
        private readonly AdminRouteRequirementContract $requirements,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $requirement = $this->requirements->forRequest($request);

        if ($requirement->open || $this->access->isSuper($user)) {
            return $next($request);
        }

        $allowed = ! $requirement->isSuperOnly()
            && collect($requirement->needs)->every(fn (array $need): bool => $this->access->allows($user, $need[0], $need[1]));

        if ($allowed) {
            return $next($request);
        }

        if ($request->routeIs('admin.dashboard') && $request->isMethod('GET')) {
            $url = $this->access->firstAllowedUrl($user);
            if ($url) {
                return redirect($url);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission for this action.'], 403);
        }

        return response()->view('admin.errors.forbidden', [
            'pageTitle' => 'No Permission',
            'breadcrumbs' => ['Admin', 'No Permission'],
            'homeUrl' => $this->access->firstAllowedUrl($user),
        ], 403);
    }
}
