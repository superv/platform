<?php

namespace Tests\Platform\Domains\Routing;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Domains\Routing\UrlGenerator;
use Tests\Platform\TestCase;
use URL;

class UrlGeneratorTest extends TestCase
{
    /** @var \SuperV\Platform\Domains\Port\Port */
    protected $port;

    /** @test */
    function platform_overrides_default_url_generator()
    {
        $this->setUpCustomPort('api.superv.io', 'v2');
        $this->makeRequest('users');

        $this->assertInstanceOf(UrlGenerator::class, url());
        $this->assertInstanceOf(UrlGenerator::class, app('url'));
    }

    protected function setUpCustomPort($hostname, $prefix = null)
    {
        $this->port = $this->setUpPort(['slug' => 'api', 'hostname' => $hostname, 'prefix' => $prefix]);
        PortDetectedEvent::dispatch($this->port);
    }

    protected function makeRequest($path = null)
    {
        $this->app->extend('request', function () use ($path) {
            return Request::create('http://'.$this->port->root().($path ? '/'.$path : ''));
        });
    }

    /** @test */
    function generates_urls_based_on_the_active_port_with_prefix()
    {
        $this->setUpCustomPort('api.superv.io', 'v2');
        $this->makeRequest('users');

        $this->assertEquals('http://api.superv.io/v2/users', URL::full());
        $this->assertEquals('http://api.superv.io/v2/users', URL::current());
        $this->assertEquals('http://api.superv.io/v2/users', URL::to('users'));
        $this->assertEquals('http://api.superv.io/v2/users', url('/users'));
    }

    /** @test */
    function generates_urls_based_on_the_active_port_without_prefix()
    {
        $this->setUpCustomPort('api.superv.io');
        $this->makeRequest('users');

        $this->assertEquals('http://api.superv.io/users', URL::full());
        $this->assertEquals('http://api.superv.io/users', URL::current());
        $this->assertEquals('http://api.superv.io/users', URL::to('users'));
    }
}