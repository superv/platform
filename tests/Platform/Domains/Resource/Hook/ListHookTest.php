<?php

namespace Tests\Platform\Domains\Resource\Hook;

use Config;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ListHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ListHookTest extends HookTestCase
{
    protected $handleExceptions = false;

    function test_resolved()
    {
        Config::set('app.debug', true);
        $_SERVER['__hooks::list.resolved'] = null;
        $categories = $this->blueprints()->categories();

        $categoryList = $this->getListComponent($categories);
        $categoryList->assertDataUrl('http://localhost/sv/res/'.$categories->getIdentifier().'/table/data');
        $categoryList->assertDataUrl($_SERVER['__hooks::list.resolved']);
    }

    function  __config()
    {
        $_SERVER['__hooks::list.config'] = null;
        $categories = $this->blueprints()->categories();

        $list = $this->getListComponent($categories);
        $this->assertEquals(['table', 'fields'], array_keys($_SERVER['__hooks::list.config']));
    }

    function  __config_is_also_hooked_before_data()
    {
        $_SERVER['__hooks::list.config.calls'] = 0;
        $categories = $this->blueprints()->categories();
        $categories->fake([], 3);

        $list = $this->getListComponent($categories);
        $this->assertEquals(1, $_SERVER['__hooks::list.config.calls']);

        $listData = $list->getData();
        $this->assertEquals(2, $_SERVER['__hooks::list.config.calls']);
    }

    function test__query_resolved()
    {
        $_SERVER['__hooks::list.query.resolved'] = null;
        $categories = $this->blueprints()->categories();
        $categories->fake([], 3);

        $this->getListComponent($categories)->getData();
        $this->assertInstanceOf(Builder::class, $_SERVER['__hooks::list.query.resolved']);
    }

    function test__data()
    {
        $_SERVER['__hooks::list.data'] = null;
        $categories = $this->blueprints()->categories();
        $categories->fake([], 3);

        $listData = $this->getListComponent($categories)->getData();

        $this->assertEquals(3, $_SERVER['__hooks::list.data']['rows']->count());
    }
}
