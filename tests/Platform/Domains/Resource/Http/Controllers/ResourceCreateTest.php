<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceCreateTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $roles = $this->schema()->roles();

        $post = ['title' => 'user'];
        $response = $this->postJsonUser($this->getCreateRoute($roles), $post);
        $response->assertOk();

        $role = $roles->first();

        $this->assertEquals('user', $role->title);
    }

    function test__validation()
    {
        $this->withExceptionHandling();

        $users = $this->schema()->users();

        $post = [
            'name' => 'Ali',
            'email' =>'ali@veli.com'
        ];
        $response = $this->postJsonUser($this->getCreateRoute($users), $post);
        $response->assertStatus(422);
    }

    /**
     * @param \SuperV\Platform\Domains\Resource\Resource $roles
     * @return string
     */
    protected function getCreateRoute(Resource $resource): string
    {
        return route('resource.create', ['resource' => $resource->getHandle()]);
    }
}

