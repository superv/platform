<?php

namespace SuperV\Platform\Domains\Auth\Access;

use SuperV\Platform\Domains\Auth\Access\Guard\Guard;
use SuperV\Platform\Domains\Auth\Access\Guard\Guardable;

/**
 * Trait HasActions
 *
 * @property \Illuminate\Database\Eloquent\Collection $roles
 */
trait HasActions
{
    public static $__cache = [];

    public function getRoles()
    {
        return $this->roles;
    }

    public function roles()
    {
        return $this->morphToMany(Role::class, 'owner', 'sv_auth_assigned_roles');
    }

    public function actions()
    {
        return $this->morphToMany(Action::class, 'owner', 'sv_auth_assigned_actions')->withPivot(['provision']);
    }

    public function allow($action)
    {
        if ($action instanceof Guardable) {
            $action = $action->guardKey();
        }
        if (! $entry = Action::withSlug($action)) {
            $entry = Action::query()->create(['slug' => $action]);
        }

        $this->actions()->syncWithoutDetaching([$entry->id => ['provision' => 'pass']]);

        static::$__cache = [];
        return $this;
    }

    public function forbid($action)
    {
        if ($action instanceof Guardable) {
            $action = $action->guardKey();
        }
        if (! $entry = Action::withSlug($action)) {
            $entry = Action::query()->create(['slug' => $action]);
        }

        $this->actions()->syncWithoutDetaching([$entry->id => ['provision' => 'fail']]);

        return $this;
    }

    public function assign(string $role)
    {
        if (! $roleEntry = Role::withSlug($role)) {
            $roleEntry = Role::create(['slug' => $role]);
        }

        $this->roles()->syncWithoutDetaching([$roleEntry->id]);

        return $roleEntry;
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

    public function can($action)
    {
        if ($action instanceof Guardable) {
            return $this->canAccess($action);
        }

        // first check forbidden
        if ($this->matchAction($action, $this->getForbiddenActions())) {
            return false;
        }

        if (in_array('*', $this->getAllowedActions())) {
            return true;
        }

        if ($this->matchAction($action, $this->getAllowedActions())) {
            return true;
        }

        if ($this->matchAction($action.'.*', $this->getAllowedActions())) {
            return true;
        }

        return false;
    }

    public function cannot($action)
    {
        return ! $this->can($action);
    }

    public function canOrFail($action)
    {
        if (! $this->can($action)) {
            AuthorizationFailedException::actionFailed($action);
        }
    }

    /**
     * Check if has access to a guardable object
     * Pass if the object is not guardable
     *
     * @param $subject
     * @return bool
     */
    public function canAccess($subject)
    {
        $guard = new Guard($this);

        return $guard->guard($subject);
    }

    /**
     * Determine if an action is valid for a given list of actions
     *
     * @param $action
     * @param $actions
     * @return bool
     */
    public function matchAction($action, $actions): bool
    {
        return in_array($action, $actions) || $this->matchWild($actions, $action);
    }

    /**
     * Determine if an action comprises and action from a given list
     * by checking :
     * 1. wildcard: module.*
     * 2. starts_with: module passes for anything starting with module
     *
     * @param $actions
     * @param $check
     * @return bool
     */
    protected function matchWild($actions, $check)
    {
        foreach ($actions as $action) {
            /**
             * Wildcard check
             */
            if (fnmatch($action, $check)) {
                return true;
            }

            /**
             *
             */
//            if (starts_with($check, $action)) {
//                return true;
//            }
        }

        return false;
    }

    /**
     * Get all assigned actions
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignedActions()
    {
        if (! isset(static::$__cache['assigned']))
            static::$__cache['assigned'] = $this->roles->map(
            function (Role $role) {
                return $role->actions;
            }
        )->flatten(1)->merge($this->actions()->get());

        return static::$__cache['assigned'];
    }

    /**
     * Get only assigned actions that are forbidden
     *
     * @return array
     */
    protected function getForbiddenActions()
    {
        $forbidden = $this->getAssignedActions()->filter(function (Action $action) {
            return $action->pivot->provision === 'fail';
        })->pluck('slug')->all();

        return $forbidden;
    }

    /**
     * Get only assigned actions that are allowed
     *
     * @return array
     */
    public function getAllowedActions()
    {
        $allowed = $this->getAssignedActions()->filter(function (Action $action) {
            return $action->pivot->provision === 'pass';
        })->pluck('slug')->all();

        return $allowed;
    }
}
