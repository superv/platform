<?php

namespace Tests\Platform\Domains\Resource\Hook;

use Tests\Platform\Domains\Resource\Fixtures\Models\TestPostModel;

/**
 * Class ResourceHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ResourceHookTest extends HookTestCase
{
    function test_config_resolved()
    {
        $_SERVER['__hooks::resource.config.resolved'] = null;
        $posts = $this->blueprints()->posts();

        $this->assertEquals(TestPostModel::class, $posts->config()->getModel());
    }

    function test__resource_resolved()
    {
        $_SERVER['__hooks::resource.resolved'] = null;
        $posts = $this->blueprints()->posts();

        $this->assertEquals($posts->getIdentifier(), $_SERVER['__hooks::resource.resolved']);
    }

    function test__query_resolved()
    {
        $_SERVER['__hooks::resource.query_resolved'] = null;
        $posts = $this->blueprints()->posts();
        $posts->newQuery();

        $this->assertNotNull($_SERVER['__hooks::resource.query_resolved']);
    }

}
