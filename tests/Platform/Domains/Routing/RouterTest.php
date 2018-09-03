<?php

namespace Tests\Platform\Domains\Routing;

use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Routing\Router;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
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

        $loader = $this->bindMock(RouteRegistrar::class);
        $loader->shouldReceive('register')->with($_SERVER['test.routes.web.foo'])->once();

        app(Router::class)->loadFromFile('tests/Platform/__fixtures__/routes/web/foo.php');
    }

    /** @test */
    function loads_route_files_from_a_path()
    {
        $path = base_path('tests/Platform/__fixtures__/routes');
        $files = app(Router::class)->portFilesIn($path);

        $this->assertArraySubset([
            'acp' => [
                $path.'/acp.php',
                $path.'/acp/foo.php',
            ],
            'web' => [
                $path.'/web/bar.php',
                $path.'/web/foo.php',
            ],
            'api' => [
                $path.'/api.php',
            ],
        ], $files);
    }

    /** @test */
    function registers_routes_from_path()
    {
        $this->setUpPorts();

        $_SERVER['test.routes.web.foo'] = ['foo/baz' => 'FooWebController@baz'];
        $_SERVER['test.routes.web.bar'] = ['bar/baz' => 'BarWebController@baz'];
        $_SERVER['test.routes.acp.foo'] = ['foo/baz' => 'FooAcpController@baz'];
        $_SERVER['test.routes.api.foo'] = ['bom/bor' => 'BomAcpController@bor'];

        $loader = $this->bindMock(RouteRegistrar::class);

        $loader->shouldReceive('setPort')->with(equalTo($this->getPort('acp')))->once();
        $loader->shouldReceive('register')->with(['foo/baz' => 'FooAcpController@baz'])->once();

        $loader->shouldReceive('setPort')->with(equalTo($this->getPort('api')))->once();
        $loader->shouldReceive('register')->with(['bom/bor' => 'BomAcpController@bor'])->once();

        $loader->shouldReceive('setPort')->with(equalTo($this->getPort('web')))->once();
        $loader->shouldReceive('register')->with(['foo/baz' => 'FooWebController@baz'])->once();
        $loader->shouldReceive('register')->with(['bar/baz' => 'BarWebController@baz'])->once();

        app(Router::class)->loadFromPath('tests/Platform/__fixtures__/routes');
    }

    protected function getPort($slug)
    {
        return Port::fromSlug($slug);
    }
}