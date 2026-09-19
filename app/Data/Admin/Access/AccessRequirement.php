<?php

namespace App\Data\Admin\Access;

/**
 * What a request needs: nothing (open), every listed section + action, or
 * — when the route could not be mapped — Super Admin only.
 */
final readonly class AccessRequirement
{
    /**
     * @param  list<array{0: string, 1: string}>  $needs
     */
    private function __construct(public bool $open, public array $needs) {}

    public static function open(): self
    {
        return new self(true, []);
    }

    /**
     * @param  list<array{0: string, 1: string}>  $needs
     */
    public static function needs(array $needs): self
    {
        return new self(false, $needs);
    }

    public static function superOnly(): self
    {
        return new self(false, []);
    }

    public function isSuperOnly(): bool
    {
        return ! $this->open && $this->needs === [];
    }
}
