<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Shared base for the admin API endpoints, all of which sit behind
 * `api.auth:admin`.
 */
abstract class AdminApiController extends ApiController
{
    protected function admin(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->role === User::ROLE_ADMIN, 401);

        return $user;
    }

    /**
     * Catalog responses are cached by version, so a write has to move it on.
     */
    protected function bumpCatalogCacheVersion(): void
    {
        Cache::forever('catalog_cache_version', ((int) Cache::get('catalog_cache_version', 1)) + 1);
    }
}
