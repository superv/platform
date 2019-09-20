<?php

namespace Tests\Platform\Domains\Resource\Hook;

/**
 * Class ListHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ListHookTest extends HookTestCase
{
    function test_resolved()
    {
        $_SERVER['__hooks::list.resolved'] = null;
        $categories = $this->blueprints()->categories();

        $categoryList = $this->getListComponent($categories);
        $categoryList->assertDataUrl('http://localhost/sv/res/'.$categories->getIdentifier().'/table/data');
        $categoryList->assertDataUrl($_SERVER['__hooks::list.resolved']);
    }
}
