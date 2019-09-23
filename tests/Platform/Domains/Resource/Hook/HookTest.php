<?php

namespace Tests\Platform\Domains\Resource\Hook;

use stdClass;
use SuperV\Platform\Domains\Resource\Hook\HookManager;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoriesDashboardPage;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoryList;
use Tests\Platform\Domains\Resource\Fixtures\Resources\CategoryObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersConfig;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFields;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormCustom;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormDefault;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostObserver;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsFields;
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostsResource;

/**
 * Class HookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookTest extends HookTestCase
{
    function test__registers_hooks_globally()
    {
        $hook = HookManager::resolve();
        $hook->register('sv.users', stdClass::class, 'UsersConfig');
        $hook->register('sv.users.fields:title', stdClass::class, 'TitleField');

        $hook = HookManager::resolve();
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
        $hook = HookManager::resolve();

        $this->assertEquals(
            [
                'forms'    => [
                    'default' => OrdersFormDefault::class,
                    'custom'  => OrdersFormCustom::class,
                ],
                'observer' => OrdersObserver::class,
            ],
            $hook->get('testing.orders')
        );

        $this->assertEquals([
            'resource' => PostsResource::class,
            'observer' => PostObserver::class,
            'fields'   => PostsFields::class,
        ], $hook->get('testing.posts'));

        $this->assertEquals([
            'lists'    => [
                'default' => CategoryList::class,
            ],
            'observer' => CategoryObserver::class,
            'pages'    => [
                'dashboard' => CategoriesDashboardPage::class,
            ],
        ], $hook->get('testing.categories'));
    }


}
