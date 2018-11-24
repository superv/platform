<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceUpdateTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $users = $this->schema()->users();

        $user = $users->fake(['group_id' => 1]);

        $this->withoutExceptionHandling();
        $response = $this->postJsonUser($user->route('update'), ['name' => 'Ali']);
        $response->assertOk();

        $user = $user->fresh();

        $this->assertEquals('Ali', $user->name);
    }
}

