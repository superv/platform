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
        $users = $this->schema()->users();
        $user = $users->fake(['name' => 'Ali Selcuk']);

        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($users->route('view', $user));
        $response->assertOk();

//        dd($response->decodeResponseJson('data'));

    }

    protected function tearDown()
    {
        Extension::unregister(TestUserResourceExtension::class);
        parent::tearDown();
    }
}


