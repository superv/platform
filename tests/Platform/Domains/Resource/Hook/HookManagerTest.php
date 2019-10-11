<?php

namespace Tests\Platform\Domains\Resource\Hook;

use stdClass;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Resource\Hook\Actions\RegisterAddonHooks;
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
use Tests\Platform\Domains\Resource\Fixtures\Resources\Posts\PostUserScope;

/**
 * Class HookManagerTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class HookManagerTest extends HookTestCase
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
            'scopes'   => [
                'user' => PostUserScope::class,
            ],
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

    function test__scans_resources_directory_when_an_addon_is_booted()
    {
        $addon = $this->setUpAddon(null, null);

        $manager = $this->bindPartialMock(HookManager::class, HookManager::resolve());
        $manager->shouldReceive('scan')->with($addon->realPath('src/Resources'))->once();

        AddonBootedEvent::dispatch($addon);
    }

    function test__scans_dddes_directory_when_an_addon_is_booted()
    {
        $addon = $this->bindMock(Addon::class);
        $addon->shouldReceive('realPath')->andReturn('path-does-not-exist');

        $manager = $this->bindPartialMock(HookManager::class, HookManager::resolve());
        $manager->shouldNotReceive('scan');

        (new RegisterAddonHooks)->handle(new AddonBootedEvent($addon));
    }

}
