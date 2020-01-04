<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ScopeHook;
use SuperV\Platform\Domains\Resource\Resource;

class PostUserScope implements ScopeHook
{
    public static $identifier = 'sv.testing.posts.scopes:user';

    public function scope($query, Resource $resource, User $user)
    {
        $_SERVER['__hooks::scope.resolved'] = [
            'query'    => $query,
            'resource' => $resource,
            'user'     => $user,
        ];
    }
}