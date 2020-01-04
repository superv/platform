<?php

namespace SuperV\Platform\Domains\Auth\Access;

interface HasActionsInterface
{
    public function cannot($action);

    public function roles();

    /**
     * Check if has access to a guardable object
     * Pass if the object is not guardable
     *
     * @param $subject
     * @return bool
     */
    public function canAccess($subject);

    /**
     * Determine if an action is valid for a given list of actions
     *
     * @param $action
     * @param $actions
     * @return bool
     */
    public function matchAction($action, $actions): bool;

    /**
     * Get only assigned actions that are allowed
     *
     * @return array
     */
    public function getAllowedActions();

    public function can($action);

    public function isA($role);

    public function isNot($role);

    public function allow($action);

    public function actions();

    /**
     * Get all assigned actions
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAssignedActions();
}
