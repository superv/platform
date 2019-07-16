<?php

namespace Tests\Platform\Platform\Domains\Resource\Fixtures\Resources\Post;

use SuperV\Platform\Domains\Resource\Config;

class PostsResource
{
    public function config(Config $config)
    {
        $config->setLabel('Posts v2');
    }
}