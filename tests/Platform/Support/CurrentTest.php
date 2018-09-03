<?php

namespace Tests\Platform\Support;

use Current;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
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

    /** @test */
    function returns_current_platform_port()
    {
        $this->setUpPort('acp', 'hostname.io');
        PortDetectedEvent::dispatch($port = Port::fromSlug('acp'));

        $this->assertEquals($port, Current::port());
    }
}