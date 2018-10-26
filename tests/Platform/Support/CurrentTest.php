<?php

namespace Tests\Platform\Support;

use Current;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use Tests\Platform\TestCase;

class CurrentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_current_logged_in_user()
    {
        $user = User::query()->create([
            'account_id' => 1,
            'id'         => rand(9, 999),
            'email'      => 'user@superv.io',
            'password' => '123'
        ]);

        $this->be($user);

        $this->assertEquals($user->fresh(), Current::user());
    }

    /** @test */
    function returns_current_platform_port()
    {
        $this->setUpPort('acp', 'hostname.io');
        PortDetectedEvent::dispatch($port = \Hub::get('acp'));

        $this->assertEquals($port, Current::port());
    }
}