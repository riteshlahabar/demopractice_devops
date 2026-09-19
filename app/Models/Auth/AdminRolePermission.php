<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRolePermission extends Model
{
    public const ACTIONS = ['view', 'create', 'edit', 'delete'];

    protected $fillable = ['admin_role_id', 'section', 'can_view', 'can_create', 'can_edit', 'can_delete'];

    protected function casts(): array
    {
        return ['can_view' => 'boolean', 'can_create' => 'boolean', 'can_edit' => 'boolean', 'can_delete' => 'boolean'];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminRole::class, 'admin_role_id');
    }

    public function allows(string $action): bool
    {
        return in_array($action, self::ACTIONS, true) && (bool) $this->getAttribute('can_'.$action);
    }
}
