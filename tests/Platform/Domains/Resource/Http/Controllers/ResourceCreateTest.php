<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceCreateTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $this->withExceptionHandling();

        $users = $this->schema()->users();

        $post = [
            'name' => 'Ali',
            'email' => 'ali@superv.io',
            'group_id' => 1
        ];
        $response = $this->postJsonUser($this->getCreateRoute($users), $post);
        $response->assertOk();

        $user = $users->first();

        $this->assertEquals('Ali', $user->name);
    }

    function test__validation()
    {
        $this->withExceptionHandling();

        $users = $this->schema()->users();
        $users->fake(['email' => 'ali@superv.io']);

        $post = [
            'name' => 'Ali Selcuk',
            'email' =>'ali@superv.io'
        ];
        $response = $this->postJsonUser($this->getCreateRoute($users), $post);
        $response->assertStatus(422);

       $this->assertEquals(
           ['email', 'group_id'],
           array_keys($response->decodeResponseJson('errors'))
       );
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

