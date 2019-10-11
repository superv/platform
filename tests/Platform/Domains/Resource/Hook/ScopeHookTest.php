<?php

namespace Tests\Platform\Domains\Resource\Hook;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class ScopeHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ScopeHookTest extends HookTestCase
{
    function test_11()
    {
        $_SERVER['__hooks::scope.resolved'] = null;

        $posts = $this->blueprints()->posts();
        $posts->newQuery();
        $this->assertNotNull($_SERVER['__hooks::scope.resolved']);

        $this->assertSame($posts, $_SERVER['__hooks::scope.resolved']['resource']);
        $this->assertInstanceOf(Builder::class, $_SERVER['__hooks::scope.resolved']['query']);
        $this->assertSame($this->testUser->getId(), $_SERVER['__hooks::scope.resolved']['user']->getId());
    }

    protected function setUp(): void
    {
        $this->afterPlatformInstalled(
            function () {
                $this->be($this->newUser());
            }
        );
        parent::setUp();
    }
}
