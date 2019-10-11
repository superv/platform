<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Resource\Resource;

interface ScopeHook
{
    public function scope($query, Resource $resource, User $user);
}