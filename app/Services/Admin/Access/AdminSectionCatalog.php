<?php

namespace App\Services\Admin\Access;

use App\Contracts\Admin\Access\AdminSectionCatalogContract;
use App\Models\Auth\AdminRolePermission;
use App\Services\Admin\Reports\ReportMenu;

/**
 * Sections come straight from the sidebar, so a new menu item is a new
 * permission row on the role screen without any extra wiring.
 */
final class AdminSectionCatalog implements AdminSectionCatalogContract
{
    /** @var array<int, array<string, mixed>>|null */
    private ?array $menu = null;

    /** @var array<string, array{key: string, label: string, actions: list<string>}>|null */
    private ?array $sections = null;

    public function __construct(private readonly ReportMenu $reports) {}

    public function menu(): array
    {
        return $this->menu ??= $this->reports->fill((array) config('admin.groups', []));
    }

    public function groups(): array
    {
        $groups = [];

        foreach ($this->menu() as $group) {
            $groupLabel = (string) (($group['label'] ?? '') === 'Navigation' ? 'Dashboard' : $group['label']);
            $direct = [];
            $subMenus = [];

            foreach ($group['items'] ?? [] as $item) {
                if (! empty($item['children'])) {
                    $children = $this->leaves($item['children']);
                    if ($children !== []) {
                        $subMenus[] = ['label' => $groupLabel.' › '.$item['label'], 'sections' => $children];
                    }
                } elseif ($section = $this->leaf($item)) {
                    $direct[] = $section;
                }
            }

            if ($direct !== []) {
                $groups[] = ['label' => $groupLabel, 'sections' => $direct];
            }
            array_push($groups, ...$subMenus);
        }

        return $groups;
    }

    public function has(string $section): bool
    {
        return isset($this->sections()[$section]);
    }

    public function actionsFor(string $section): array
    {
        $configured = (array) config('admin_access.section_actions', []);
        foreach ($configured as $pattern => $actions) {
            if ($pattern === $section || (str_ends_with($pattern, '*') && str_starts_with($section, rtrim($pattern, '*')))) {
                return array_values((array) $actions);
            }
        }

        $module = config('admin.modules.'.$this->moduleOf($section));
        if (! is_array($module)) {
            return AdminRolePermission::ACTIONS;
        }

        return array_values(array_filter(AdminRolePermission::ACTIONS, fn (string $action): bool => match ($action) {
            'create' => ($module['can_create'] ?? true) !== false,
            'edit' => ($module['can_edit'] ?? true) !== false,
            'delete' => ($module['can_delete'] ?? true) !== false,
            default => true,
        }));
    }

    /**
     * customer-orders → orders; any other section is its own module key.
     */
    private function moduleOf(string $section): string
    {
        foreach ((array) config('admin_access.channels', []) as $channel) {
            $module = substr($section, strlen($channel) + 1);
            if (str_starts_with($section, $channel.'-') && in_array($module, (array) config('admin_access.channel_modules', []), true)) {
                return $module;
            }
        }

        return $section;
    }

    /**
     * @return array<string, array{key: string, label: string, actions: list<string>}>
     */
    private function sections(): array
    {
        if ($this->sections === null) {
            $this->sections = [];
            foreach ($this->groups() as $group) {
                foreach ($group['sections'] as $section) {
                    $this->sections[$section['key']] = $section;
                }
            }
        }

        return $this->sections;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array{key: string, label: string, actions: list<string>}>
     */
    private function leaves(array $items): array
    {
        return array_values(array_filter(array_map(fn (array $item): ?array => $this->leaf($item), $items)));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{key: string, label: string, actions: list<string>}|null
     */
    private function leaf(array $item): ?array
    {
        if (empty($item['route']) || empty($item['key'])) {
            return null;
        }

        return ['key' => (string) $item['key'], 'label' => (string) $item['label'], 'actions' => $this->actionsFor((string) $item['key'])];
    }
}
