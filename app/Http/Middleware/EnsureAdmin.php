<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== User::ROLE_ADMIN || $request->user()->status !== 'active') {
            auth()->logout();

            return redirect()->route('admin.login')->with('error', 'Please sign in with an active administrator account.');
        }

        return $next($request);
    }
}
