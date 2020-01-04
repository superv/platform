<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ConfigResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\QueryResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ResourceResolvedHook;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;

class Posts implements ConfigResolvedHook, ResourceResolvedHook, QueryResolvedHook
{
    public static $identifier = 'sv.testing.posts';

    public function configResolved(ResourceConfig $config)
    {
        $config->model(TestPostModel::class);
    }

    public function resolved(Resource $resource)
    {
        $_SERVER['__hooks::resource.resolved'] = $resource->getIdentifier();
    }

    public function queryResolved($query)
    {
        $_SERVER['__hooks::resource.query_resolved'] = $query;
    }
}
