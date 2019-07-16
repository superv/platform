<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Hook;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Platform\Domains\Resource\Fixtures\Resources\Post\PostsResource;

/**
 * Class HookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookTest extends ResourceTestCase
{
    function test__extends_resource()
    {
        $this->schema()->posts();

        Hook::register('t_posts', PostsResource::class);

        $posts = ResourceFactory::make('t_posts');

        $this->assertEquals('Posts v2', $posts->config()->getLabel());
    }

    protected function tearDown()
    {
        parent::tearDown();

        Hook::unregister('t_posts');
    }
}
