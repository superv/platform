<?php

namespace Tests\Platform\Platform\Domains\Resource\Fixtures\Resources\Post;

use SuperV\Platform\Domains\Resource\ResourceConfig;

class PostsResource
{
    public function config(ResourceConfig $config)
    {
        $config->label('Posts v2');
    }
}