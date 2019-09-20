<?php

namespace Tests\Platform\Domains\Resource\Hook;

use stdClass;
use SuperV\Platform\Domains\Resource\Hook\Hook;
use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoryList;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoryObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersConfig;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFields;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormCustom;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormDefault;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsConfig;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsFields;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class HookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookTest extends ResourceTestCase
{
    function test__registers_hooks_globally()
    {
        $hook = Hook::resolve();
        $hook->register('sv.users', stdClass::class, 'UsersConfig');
        $hook->register('sv.users.fields:title', stdClass::class, 'TitleField');

        $hook = Hook::resolve();
        $this->assertEquals(
            [
                'config' => stdClass::class,
                'fields' => [
                    'title' => stdClass::class,
                ],
            ],
            $hook->get('sv.users')
        );

        $this->assertEquals(
            [
                'title' => stdClass::class,
            ],
            $hook->get('sv.users', 'fields')
        );
        $this->assertEquals(stdClass::class, $hook->get('sv.users', 'config'));
    }

    function test__scan_path_for_hooks()
    {
        $hook = Hook::resolve();

        $this->assertEquals(
            [
                'config'   => OrdersConfig::class,
                'forms'    => [
                    'default' => OrdersFormDefault::class,
                    'custom'  => OrdersFormCustom::class,
                ],
                'observer' => OrdersObserver::class,
            ],
            $hook->get('testing.orders')
        );

        $this->assertEquals([
            'config'   => PostsConfig::class,
            'observer' => PostObserver::class,
            'fields'   => PostsFields::class,
        ], $hook->get('testing.posts'));

        $this->assertEquals([
            'lists'    => [
                'default' => CategoryList::class,
            ],
            'observer' => CategoryObserver::class,
        ], $hook->get('testing.categories'));
    }

    function test_config_hook()
    {
        $orders = $this->makeResource('testing.orders');
        $posts = $this->makeResource('testing.posts');

        $this->assertEquals('Orders Hooked', $orders->config()->getLabel());

        $this->assertEquals('Posts Hooked', $posts->config()->getLabel());
        $this->assertEquals(TestPostModel::class, $posts->config()->getModel());
    }

    protected function setUp()
    {
        parent::setUp();

        Hook::resolve()->scan(__DIR__.'/../Fixtures/Resources');
    }

    protected function tearDown()
    {
        Hook::resolve()->flush();

        parent::tearDown();
    }
}
