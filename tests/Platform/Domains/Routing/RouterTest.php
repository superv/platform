<?php

namespace Tests\SuperV\Platform\Domains\Routing;

use Mockery;
use SuperV\Platform\Domains\Routing\RouteLoader;
use SuperV\Platform\Domains\Routing\Router;
use Tests\SuperV\Platform\BaseTestCase;

class RouterTest extends BaseTestCase
{
    /** @test */
    function registers_route_files()
    {
        $_SERVER['test.routes.web.foo'] = [
            'foo/bar' => 'FooWebController@bar',
            'foo/baz' => 'FooWebController@baz',
        ];

        $loader = $this->app->instance(RouteLoader::class, Mockery::mock(RouteLoader::class));
        $loader->shouldReceive('load')->with($_SERVER['test.routes.web.foo'])->once();

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

        $loader = $this->app->instance(RouteLoader::class, Mockery::mock(RouteLoader::class));

        $loader->shouldReceive('load')->with(['foo/baz' => 'FooWebController@baz',], 'web')->once();
        $loader->shouldReceive('load')->with(['bar/baz' => 'BarWebController@baz',], 'web')->once();
        $loader->shouldReceive('load')->with(['foo/baz' => 'FooAcpController@baz'], 'acp')->once();

        app(Router::class)->loadFromPath('tests/Platform/__fixtures__/routes');
    }
}