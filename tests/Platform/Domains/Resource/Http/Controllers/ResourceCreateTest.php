<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceCreateTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $roles = $this->schema()->roles();

        $this->withoutExceptionHandling();

        $url = route('resource.create', ['resource' => $roles->getHandle()]);
        $response = $this->postJsonUser($url, ['title' => 'user']);
        $response->assertOk();

        $role = $roles->first();

        $this->assertEquals('user', $role->title);
    }
}

