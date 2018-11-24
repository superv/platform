<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsMatchingResources;
use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Extension\RegisterExtensionsInPath;
use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\Fixtures\Extension\TestMultipleResourcesArrayExtension;
use Tests\Platform\Domains\Resource\Fixtures\Extension\TestMultipleResourcesPatternExtension;
use Tests\Platform\Domains\Resource\Fixtures\Extension\TestUserResourceExtension;

class ExtensionTest extends ResourceTestCase
{
    function test__extends_resource()
    {
        $this->makeResource('t_users');
        Extension::register(TestUserResourceExtension::class);

        $extended = Resource::of('t_users');

        $nameField = $extended->getField('name');

        $this->assertTrue($nameField->getConfigValue('extended'));
    }

    function test__extends_multiple_resources_with_pattern()
    {
        $this->makeResource('test_users');
        $this->makeResource('test_posts');
        $this->makeResource('t_forms');

        Extension::register(TestMultipleResourcesPatternExtension::class);

        $users = Resource::of('test_users');
        $posts = Resource::of('test_posts');
        $forms = Resource::of('t_forms');

        $this->assertTrue($users->getConfigValue('extended'));
        $this->assertTrue($posts->getConfigValue('extended'));
        $this->assertNotTrue($forms->getConfigValue('extended'));
    }

    function test__extends_multiple_resources_with_array()
    {
        $this->makeResource('test_users');
        $this->makeResource('test_posts');
        $this->makeResource('t_forms');

        Extension::register(TestMultipleResourcesArrayExtension::class);

        $users = Resource::of('test_users');
        $posts = Resource::of('test_posts');
        $forms = Resource::of('t_forms');

        $this->assertTrue($users->getConfigValue('extended'));
        $this->assertTrue($posts->getConfigValue('extended'));
        $this->assertNotTrue($forms->getConfigValue('extended'));
    }

    function test__observes_retrieved()
    {
        $this->makeResource('t_users');
        Extension::register(TestUserResourceExtension::class);

        $user = Resource::of('t_users')->fake();

        $this->assertEquals($user->fresh(), TestUserResourceExtension::$called['retrieved']);
    }

    function test__observes_saving()
    {
        $this->makeResource('t_users');
        Extension::register(TestUserResourceExtension::class);

        $user = Resource::of('t_users')->fake();

        $this->assertEquals($user, TestUserResourceExtension::$called['saving']);
    }

    function test__observes_saved()
    {
        $this->makeResource('t_users');
        Extension::register(TestUserResourceExtension::class);

        $user = Resource::of('t_users')->fake();

        $this->assertEquals($user, TestUserResourceExtension::$called['saved']);
    }

    function test__registers_extensions_from_path()
    {
        RegisterExtensionsInPath::dispatch(
            __DIR__.'/Fixtures/Extensions',
            'Tests\Platform\Domains\Resource\Fixtures\Extensions'
        );

        $this->assertNotNull(Extension::get('test_a'));
    }

    protected function tearDown()
    {
        parent::tearDown();

        Extension::unregister(TestUserResourceExtension::class);
    }
}
