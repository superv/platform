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

    function test_returns_current_logged_in_user()
    {
        $user = User::query()->create([
            'id'       => rand(9, 999),
            'name'     => 'User Name',
            'email'    => 'user@superv.io',
            'password' => '123',
        ]);

        $this->be($user);

        $this->assertEquals($user->fresh(), Current::user());
    }

    function test_request_path()
    {
        $this->setUpCustomPort('api.superv.io', 'v2');
        $this->makeRequest('users');

        $this->assertEquals('/users', Current::requestPath());
    }

    function test_returns_current_platform_port()
    {
        $this->setUpPort(['slug' => 'acp', 'hostname' => 'hostname.io']);
        PortDetectedEvent::dispatch($port = \Hub::get('acp'));

        $this->assertEquals($port, Current::port());
    }

    function test_returns_current_url()
    {
        $this->setUpPort(['slug' => 'acp', 'hostname' => 'localhost']);
        PortDetectedEvent::dispatch($port = \Hub::get('acp'));

        $this->assertEquals('http://localhost', Current::url());
        $this->assertEquals('http://localhost/sv/url', Current::url('sv/url'));
    }
}