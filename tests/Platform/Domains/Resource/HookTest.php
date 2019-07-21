<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Extension\RegisterHooksInPath;
use SuperV\Platform\Domains\Resource\Hook;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\Fixtures\TestPost;

/**
 * Class HookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookTest extends ResourceTestCase
{
    function test__registers_extensions_from_path()
    {
        $this->registerHooksBase();

        $this->assertNotNull(Hook::base('posts'));
    }

    function test_hooks_config()
    {
        $this->registerHooksBase();

        $this->makeResource('posts');

        $posts = ResourceFactory::make('posts');

        $this->assertEquals('Posts v2', $posts->config()->getLabel());
        $this->assertEquals(TestPost::class, $posts->config()->getModel());
    }

    protected function registerHooksBase(): void
    {
        RegisterHooksInPath::dispatch(
            __DIR__.'/Fixtures/Resources',
            'Tests\Platform\Domains\Resource\Fixtures\Resources'
        );
    }
}
