<?php

namespace Tests\Platform\Domains\Port;

use Hub;
use SuperV\Platform\Domains\Port\Port;
use Tests\Platform\TestCase;

class HubTest extends TestCase
{
    function test_registers_port_and_resolves_back()
    {
        $registered = Hub::register(TestApiPort::class);

        $port = Hub::get('api');

        $this->assertInstanceOf(TestApiPort::class, $port);
        $this->assertEquals($registered, $port);
    }

    function test_returns_all_registered_ports()
    {
        $apiPort = Hub::register(TestApiPort::class);
        $acpPort = Hub::register(TestAcpPort::class);

        $ports = Hub::ports();

        $this->assertEquals(2, $ports->count());
        $this->assertContains($apiPort, $ports);
        $this->assertContains($acpPort, $ports);
    }
}

class TestApiPort extends Port
{
    protected $slug = 'api';
}

class TestAcpPort extends Port
{
    protected $slug = 'acp';
}
