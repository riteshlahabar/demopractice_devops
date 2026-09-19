<?php

namespace App\Contracts\Admin;

/**
 * SRP: the shape of a module's field list - what nests inside what, what is
 * rendered at all, and which subsections a container declares.
 */
interface FormFieldTreeContract
{
    /**
     * Nests any field carrying `render_inside` under the field it names, which
     * is how the product "Bottom Details" block gets its subsections.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<int, array{field: array<string, mixed>, children: array<int, array<string, mixed>>}>
     */
    public function build(array $fields): array;

    /**
     * @param  array<string, mixed>  $field
     */
    public function shouldRender(array $field, bool $editing): bool;

    /**
     * Subsections declared by a container field, keyed by their `render_group`.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, string>
     */
    public function groups(array $field): array;
}
