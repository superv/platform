<?php

namespace Tests\Platform\Domains\Routing;

use Hub;
use SuperV\Platform\Domains\Routing\Router;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use Tests\Platform\TestCase;

/**
 * Class RouterTest
 *
 * @package Tests\Platform\Domains\Routing
 * @group   resource
 */
class RouterTest extends TestCase
{
    function test__registers_route_files()
    {
        $_SERVER['test.routes.web.foo'] = [
            'foo/bar' => 'FooWebController@bar',
            'foo/baz' => 'FooWebController@baz',
        ];

        $loader = $this->bindMock(RouteRegistrar::class);
        $loader->shouldReceive('register')->with($_SERVER['test.routes.web.foo'])->once()->andReturnSelf();

        app(Router::class)->loadFromFile('tests/Platform/__fixtures__/routes/web/foo.php');
    }

    function test__loads_route_files_from_a_path()
    {
        $path = base_path('tests/Platform/__fixtures__/routes');
        $files = app(Router::class)->portFilesIn($path);

        $this->assertArrayContains([
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

    function test__registers_routes_from_path()
    {
        $this->setUpPorts();

        $_SERVER['test.routes.web.foo'] = ['foo/baz' => 'FooWebController@baz'];
        $_SERVER['test.routes.web.bar'] = ['bar/baz' => 'BarWebController@baz'];
        $_SERVER['test.routes.acp.foo'] = ['foo/baz' => 'FooAcpController@baz'];
        $_SERVER['test.routes.api.foo'] = ['bom/bor' => 'BomAcpController@bor'];

        $registrar = $this->bindMock(RouteRegistrar::class);

        $registrar->shouldReceive('setPort')->with(Hub::get('acp'))->once();
        $registrar->shouldReceive('globally')->with(false)->once();
        $registrar->shouldReceive('register')->with(['foo/baz' => 'FooAcpController@baz'])->once()->andReturnSelf();

        $registrar->shouldReceive('setPort')->with(Hub::get('api'))->once();
        $registrar->shouldReceive('globally')->with(false)->once();
        $registrar->shouldReceive('register')->with(['bom/bor' => 'BomAcpController@bor'])->once()->andReturnSelf();

        $registrar->shouldReceive('setPort')->with(Hub::get('web'))->once();
        $registrar->shouldReceive('globally')->with(false)->once();
        $registrar->shouldReceive('register')->with(['foo/baz' => 'FooWebController@baz'])->once()->andReturnSelf();
        $registrar->shouldReceive('register')->with(['bar/baz' => 'BarWebController@baz'])->once()->andReturnSelf();

        $path = base_path('tests/Platform/__fixtures__/routes');
        Router::resolve()->loadFromPath($path);
    }

    protected function getPort($slug)
    {
        return \Hub::get($slug);
    }
}
