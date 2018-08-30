<?php

namespace Tests\Platform\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\User;
use Current;
use Tests\Platform\TestCase;

class CurrentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_current_logged_in_user()
    {
        $user = factory(User::class)->create([
            'account_id' => 1,
            'id'         => rand(9, 999),
            'email'      => 'user@superv.io',
        ]);

        $this->be($user);

        $this->assertEquals($user->fresh(), Current::user());
    }
}