<?php

namespace Tests\SuperV\Platform\Domains\Routing;

use SuperV\Platform\Domains\Routing\RouteLoader;
use SuperV\Platform\Domains\Routing\Router;
use Tests\SuperV\Platform\BaseTestCase;
use Mockery as m;

class RouterTest extends BaseTestCase
{
    /**
     * @test
     */
    function registers_route_files()
    {
        $loader = m::mock(RouteLoader::class);
        $this->app->singleton(RouteLoader::class, function() use ($loader) { return $loader;});

        $loader->shouldReceive('load')->with([
            'foo/bar' => 'FooController@bar',
            'foo/baz' => 'FooController@baz',
        ])->once();

        $router = app(Router::class);
        $router->loadFromFile('tests/Platform/__fixtures__/routes/web/foo.php');
    }

}