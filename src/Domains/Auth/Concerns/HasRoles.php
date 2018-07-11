<?php

namespace SuperV\Platform\Domains\Auth\Concerns;

use SuperV\Platform\Domains\Auth\Role;

/**
 * Trait HasRoles
 *
 * @property \Illuminate\Database\Eloquent\Collection $roles
 * @package SuperV\Platform\Domains\Auth\Concerns
 */
trait HasRoles
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, str_singular($this->getTable()).'_roles');
    }

    public function assign(string $role)
    {
        if (! $roleEntry = Role::withSlug($role)) {
            $roleEntry = Role::create(['slug' => $role]);
        }

        $this->roles()->syncWithoutDetaching([$roleEntry->id]);

        return $this;
    }

    public function isA($role)
    {
        return $this->roles->pluck('slug')->contains($role);
    }

    public function isAn($role)
    {
        return $this->isA($role);
    }

    public function isNotA($role)
    {
        return ! $this->isA($role);
    }

    public function isNotAn($role)
    {
        return ! $this->isA($role);
    }
}