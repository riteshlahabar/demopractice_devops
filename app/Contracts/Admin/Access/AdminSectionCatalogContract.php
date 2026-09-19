<?php

namespace App\Contracts\Admin\Access;

/**
 * Every admin section a role can be given permissions on: each sidebar item
 * (sub-items and each report included) with the actions it supports.
 */
interface AdminSectionCatalogContract
{
    /**
     * Sidebar groups as configured, with the Reports group filled in.
     *
     * @return array<int, array<string, mixed>>
     */
    public function menu(): array;

    /**
     * Sections for the role screen, grouped like the sidebar.
     *
     * @return list<array{label: string, sections: list<array{key: string, label: string, actions: list<string>}>}>
     */
    public function groups(): array;

    public function has(string $section): bool;

    /**
     * @return list<string> subset of view, create, edit, delete
     */
    public function actionsFor(string $section): array;
}
