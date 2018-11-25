<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Extension\Extension;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
use SuperV\Platform\Domains\UI\Components\Layout\RowComponent;
use Tests\Platform\Domains\Resource\Fixtures\Extension\TestUserResourceExtension;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceViewTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        Extension::register(TestUserResourceExtension::class);

        $users = $this->schema()->users();

        $user = $users->fake(['name' => 'Ali Selcuk']);

        $view = $users->resolveView($user);
        $this->assertInstanceOf(ResourceView::class, $view);

        $heading = $view->getHeading();
        $this->assertInstanceOf(RowComponent::class, $heading);

        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($users->route('view', $user));
        $response->assertOk();

        $this->assertEquals(sv_compose($heading), $response->original['data']['props']['blocks'][0]);
    }
}


