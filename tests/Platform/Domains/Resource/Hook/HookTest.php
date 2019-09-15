<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Hook\Hook;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersConfig;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFields;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class HookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookTest extends ResourceTestCase
{
    function test__resolves_from_container_as_a_singleton()
    {
        $hook = Hook::resolve();
        $hook->register('sv::users', 'config', 'config_class');
        $hook->register('sv::users', 'fields', 'fields_class');

        $hook = Hook::resolve();
        $this->assertEquals(
            [
                'config' => 'config_class',
                'fields' => 'fields_class',
            ],
            $hook->get('sv::users')
        );

        $this->assertEquals('fields_class', $hook->get('sv::users', 'fields'));
        $this->assertEquals('config_class', $hook->get('sv::users', 'config'));
    }

    function test__scan_path_for_hooks()
    {
        $hook = Hook::resolve();

        $this->assertEquals(
            [
                'config'   => OrdersConfig::class,
                'fields'   => OrdersFields::class,
                'observer' => OrdersObserver::class,
            ],
            $hook->get('testing::orders')
        );

        $this->assertEquals([
            'config'   => PostsConfig::class,
            'observer' => PostObserver::class,
        ], $hook->get('testing::posts'));
    }

    function test_merges_config_from_hook()
    {
        $orders = $this->makeResource('testing::orders');
        $posts = $this->makeResource('testing::posts');

        $this->assertEquals('Orders Hooked', $orders->config()->getLabel());

        $this->assertEquals('Posts Hooked', $posts->config()->getLabel());
        $this->assertEquals(TestPostModel::class, $posts->config()->getModel());
    }

    protected function setUp()
    {
        parent::setUp();

        Hook::resolve()->scan(__DIR__.'/../Fixtures/Resources');
    }
}
