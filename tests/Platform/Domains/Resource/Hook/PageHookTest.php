<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\UI\ResourceDashboard;

/**
 * Class PageHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class PageHookTest extends HookTestCase
{
    function test_resolved()
    {
        $_SERVER['__hooks::pages.dashboard.resolved'] = null;
        $categories = $this->blueprints()->categories();

        ResourceDashboard::resolve($categories);

        $this->assertNotNull($_SERVER['__hooks::pages.dashboard.resolved']);
        $this->assertNull($_SERVER['__hooks::pages.dashboard.rendered'] ?? null);
    }

    function test_rendered()
    {
        $_SERVER['__hooks::pages.dashboard.rendered'] = null;
        $categories = $this->blueprints()->categories();

        ResourceDashboard::resolve($categories)->render();

        $this->assertNotNull($_SERVER['__hooks::pages.dashboard.rendered']);
        $this->assertTrue($_SERVER['__hooks::pages.dashboard.rendered']['built']);
    }
}
