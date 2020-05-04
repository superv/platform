<?php

namespace Tests\Platform\Domains\Routing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Routing\UrlGenerator;
use Tests\Platform\TestCase;
use URL;

/**
 * Class UrlGeneratorTest
 *
 * @package Tests\Platform\Domains\Routing
 * @group   resource
 */
class UrlGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @var \SuperV\Platform\Domains\Port\Port */
    protected $port;

    function platform_overrides_default_url_generator()
    {
        $this->setUpCustomPort('api.superv.io', 'v2');
        $this->makeRequest('users');

        $this->assertInstanceOf(UrlGenerator::class, url());
        $this->assertInstanceOf(UrlGenerator::class, app('url'));
    }

    function test__generates_urls_based_on_the_active_port_with_prefix()
    {
        $this->setUpCustomPort('api.superv.io', 'v2');
        $this->makeRequest('users');
        $url = sv_url();


        $this->assertEquals('http://api.superv.io/v2/users', $url->full());
        $this->assertEquals('http://api.superv.io/v2/users', $url->current());
        $this->assertEquals('http://api.superv.io/v2/users', $url->to('users'));
        $this->assertEquals('http://api.superv.io/v2/users', sv_url('users'));
    }

    function test__generates_urls_based_on_the_active_port_without_prefix()
    {
        $this->setUpCustomPort('api.superv.io');
        $this->makeRequest('users');

        $this->assertEquals('http://api.superv.io/users', URL::full());
        $this->assertEquals('http://api.superv.io/users', URL::current());
        $this->assertEquals('http://api.superv.io/users', URL::to('users'));
    }
}