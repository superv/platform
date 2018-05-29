<?php

namespace Tests\Platform\Domains\Routing;

use Mockery;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use SuperV\Platform\Domains\Routing\Router;
use Tests\Platform\TestCase;

class RouterTest extends TestCase
{
    /** @test */
    function registers_route_files()
    {
        $_SERVER['test.routes.web.foo'] = [
            'foo/bar' => 'FooWebController@bar',
            'foo/baz' => 'FooWebController@baz',
        ];

        $loader = $this->app->instance(RouteRegistrar::class, Mockery::mock(RouteRegistrar::class));
        $loader->shouldReceive('register')->with($_SERVER['test.routes.web.foo'])->once();

        app(Router::class)->loadFromFile('tests/Platform/__fixtures__/routes/web/foo.php');
    }

    /** @test */
    function registers_port_routes()
    {
        $_SERVER['test.routes.web.foo'] = [
            'foo/baz' => 'FooWebController@baz',
        ];
        $_SERVER['test.routes.web.bar'] = [
            'bar/baz' => 'BarWebController@baz',
        ];
        $_SERVER['test.routes.acp.foo'] = [
            'foo/baz' => 'FooAcpController@baz',
        ];

        $loader = $this->app->instance(RouteRegistrar::class, Mockery::mock(RouteRegistrar::class));

        $loader->shouldReceive('setPort')->with('web')->once();
        $loader->shouldReceive('register')->with(['foo/baz' => 'FooWebController@baz'])->once();
        $loader->shouldReceive('register')->with(['bar/baz' => 'BarWebController@baz'])->once();

        $loader->shouldReceive('setPort')->with('acp')->once();
        $loader->shouldReceive('register')->with(['foo/baz' => 'FooAcpController@baz'])->once();

        app(Router::class)->loadFromPath('tests/Platform/__fixtures__/routes');
    }
}