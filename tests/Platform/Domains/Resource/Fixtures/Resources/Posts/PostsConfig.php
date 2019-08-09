<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Posts;

use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;

class PostsConfig extends ResourceConfig
{
    protected $label =  'Posts v2';

    protected $model = TestPostModel::class;

}
