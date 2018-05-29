<?php

namespace Tests\Platform\Domains\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\User;
use Tests\Platform\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function assigns_roles_to_user()
    {
        /** @var \SuperV\Platform\Domains\Auth\User $user */
        $user = factory(User::class)->create();

        $user->assign('client');

        $this->assertTrue($user->isA('client'));
        $this->assertTrue($user->isAn('client'));
        $this->assertTrue($user->isNotA('admin'));
        $this->assertTrue($user->isNotAn('admin'));
    }
}