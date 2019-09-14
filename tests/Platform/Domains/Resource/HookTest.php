<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Extension\RegisterHooksInPath;
use SuperV\Platform\Domains\Resource\Hook;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;

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
        $this->registerHooksBase('hook-ns');

        $this->assertNotNull(Hook::base('hook-ns::posts'));
    }

    function test_hook_config()
    {
        $this->registerHooksBase('platform');

        $posts = $this->makeResource('posts');

        $this->assertEquals('Posts v2', $posts->config()->getLabel());
        $this->assertEquals(TestPostModel::class, $posts->config()->getModel());
    }

    function test_hook_saving_event()
    {
        $this->registerHooksBase('platform');

        $posts = $this->makeResource('posts', ['title:text']);
        $post = $posts->create(['title' => 'Post']);
        $this->assertEquals('Post Before', $post->title);
    }

    function test_hook_saved_event()
    {
        $this->registerHooksBase('platform');

        $orders = $this->makeResource('orders', ['title:text']);
        $order = $orders->create(['title' => 'Order']);
        $this->assertEquals('Order After', $order->title);
    }

    protected function registerHooksBase($namespace): void
    {
        RegisterHooksInPath::dispatch(
            $namespace,
            __DIR__.'/Fixtures/Resources',
            'Tests\Platform\Domains\Resource\Fixtures\Resources'
        );
    }
}
