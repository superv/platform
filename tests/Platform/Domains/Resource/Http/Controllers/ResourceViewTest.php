<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Extension\Extension;
use Tests\Platform\Domains\Resource\Fixtures\Extension\TestUserResourceExtension;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceViewTest extends ResourceTestCase
{
    use ResponseHelper;

    function test__bsmllh()
    {
        $users = $this->schema()->users();
        $user = $users->fake(['name' => 'Ali Selcuk']);

        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($users->route('view', $user));
        $response->assertOk();

        $page = HelperComponent::from($response->decodeResponseJson('data'));
        $view = HelperComponent::from($page->getProp('blocks.0'));

        $this->assertNotNull($view->getProp('fields'));
    }

    protected function tearDown()
    {
        Extension::unregister(TestUserResourceExtension::class);
        parent::tearDown();
    }
}


