<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;

class PostsConfig
{
    public static $identifier = 'testing::posts';

    public function resolved(ResourceConfig $config)
    {
        $config->label('Posts Hooked');
        $config->model(TestPostModel::class);
    }
}
