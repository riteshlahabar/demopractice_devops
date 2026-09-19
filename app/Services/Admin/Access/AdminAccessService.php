<?php

namespace App\Services\Admin\Access;

use App\Contracts\Admin\Access\AdminAccessContract;
use App\Contracts\Admin\Access\AdminRouteRequirementContract;
use App\Contracts\Admin\Access\AdminSectionCatalogContract;
use App\Models\Auth\AdminRole;
use App\Models\User;

/**
 * Answers "may this admin do X in section Y". Only an active admin account
 * with a role gets anything; Super Admin gets everything; any other role
 * gets exactly the ticked boxes, limited to actions the section offers.
 */
final class AdminAccessService implements AdminAccessContract
{
    /** @var array<int|string, AdminRole|null> */
    private array $roles = [];

    public function __construct(
        private readonly AdminSectionCatalogContract $catalog,
        private readonly AdminRouteRequirementContract $routes,
    ) {}

    public function isSuper(User $user): bool
    {
        return (bool) $this->role($user)?->is_super;
    }

    public function allows(User $user, string $section, string $action): bool
    {
        $role = $this->role($user);
        if (! $role) {
            return false;
        }
        if ($role->is_super) {
            return true;
        }
        if (! in_array($action, $this->catalog->actionsFor($section), true)) {
            return false;
        }

        $permission = $role->permissions->firstWhere('section', $section);

        return (bool) $permission?->allows($action);
    }

    public function moduleAbilities(User $user, string $moduleKey, ?string $channel): array
    {
        $sections = $this->routes->sectionsForModule($moduleKey, $channel);
        $abilities = [];

        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            $abilities[$action] = collect($sections)->every(fn (string $section): bool => $this->allows($user, $section, $action));
        }

        return $abilities;
    }

    public function menuFor(User $user): array
    {
        $groups = [];

        foreach ($this->catalog->menu() as $group) {
            $items = [];
            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['children'])) {
                    $item['children'] = array_values(array_filter($item['children'], fn (array $child): bool => $this->canOpen($user, $child)));
                    if ($item['children'] !== []) {
                        $items[] = $item;
                    }
                } elseif ($this->canOpen($user, $item)) {
                    $items[] = $item;
                }
            }

            if ($items !== []) {
                $groups[] = ['items' => $items] + $group;
            }
        }

        return $groups;
    }

    public function firstAllowedUrl(User $user): ?string
    {
        foreach ($this->menuFor($user) as $group) {
            foreach ($group['items'] as $item) {
                $target = ! empty($item['children']) ? $item['children'][0] : $item;
                if (! empty($target['route'])) {
                    return route($target['route'], $target['params'] ?? []);
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function canOpen(User $user, array $item): bool
    {
        return ! empty($item['route']) && $this->allows($user, (string) $item['key'], 'view');
    }

    private function role(User $user): ?AdminRole
    {
        if ($user->role !== User::ROLE_ADMIN || $user->status !== 'active' || ! $user->admin_role_id) {
            return null;
        }

        $key = $user->getKey().':'.$user->admin_role_id;
        if (! array_key_exists($key, $this->roles)) {
            $this->roles[$key] = $user->relationLoaded('adminRole') && $user->adminRole?->relationLoaded('permissions')
                ? $user->adminRole
                : AdminRole::query()->with('permissions')->find($user->admin_role_id);
        }

        return $this->roles[$key];
    }
}
