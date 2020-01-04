<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Resource\Hook\Contracts\HookByRole;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ResourceResolvedHook;
use SuperV\Platform\Domains\Resource\Resource;

class ManagerPosts implements ResourceResolvedHook, HookByRole
{
    public static $identifier = 'sv.testing.posts';

    public function resolved(Resource $resource)
    {
        $_SERVER['__hooks::resource.manager.resolved'] = $resource->getIdentifier().'.role:manager';
    }

    public static function getRole(): string
    {
        return 'manager';
    }
}
