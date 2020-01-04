<?php

namespace SuperV\Platform\Domains\Auth\Contracts;

interface User
{
    public function getEmail();

    public function updatePassword($newPassword);

    public function assign(string $role);

    public function isA($role): bool;

    public function isNotA($role): bool;

    public function can($action): bool;

    public function canNot($action): bool;

    public function canOrFail($action);

    public function forbidden($action): bool;
}