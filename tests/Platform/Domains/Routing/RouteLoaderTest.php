<?php

namespace Tests\SuperV\Platform\Domains\Routing;

use SuperV\Platform\Domains\Routing\RouteLoader;
use Tests\SuperV\Platform\BaseTestCase;

class RouteLoaderTest extends BaseTestCase
{
    /**
     * @test
     */
    function loads_routes_from_array()
    {
        $this->make(RouteLoader::class)
             ->load([
                 'web/foo'       => 'WebController@foo',
                 'web/bar'       => [
                     'uses' => 'WebController@bar',
                     'as'   => 'web.bar',
                 ],
                 'post@web/foo'  => 'WebController@postFoo',
                 'patch@web/bar' => function () { },
             ]);

        $getRoutes = $this->router()->getRoutes()->get('GET');

        $this->assertEquals('WebController@foo', $getRoutes['web/foo']->getAction('controller'));
        $this->assertEquals('WebController@bar', $getRoutes['web/bar']->getAction('controller'));
        $this->assertEquals('web.bar', $getRoutes['web/bar']->getName());

        $postRoutes = $this->router()->getRoutes()->get('POST');
        $this->assertEquals('WebController@postFoo', $postRoutes['web/foo']->getAction('controller'));

        $patchRoutes = $this->router()->getRoutes()->get('PATCH');
        $this->assertInstanceOf(\Closure::class, $patchRoutes['web/bar']->getAction('uses'));
    }

    /**
     * @test
     */
    function loads_routes_for_a_port()
    {
        $this->setUpPorts();

        $loader = $this->app->make(RouteLoader::class);
        $loader->load(['web/foo' => 'WebController@foo'], 'web');
        $loader->load(['acp/foo' => 'AcpController@foo'], 'acp');
        $loader->load(['api/foo' => 'ApiController@foo'], 'api');

        $getRoutes = $this->router()->getRoutes()->get('GET');

        $webRoute = $getRoutes['superv.ioweb/foo'];
        $this->assertEquals('web', $webRoute->getAction('port'));
        $this->assertEquals('superv.io', $webRoute->getDomain());
        $this->assertNull($webRoute->getPrefix());

        $acpRoute = $getRoutes['superv.ioacp/acp/foo'];
        $this->assertEquals('acp', $acpRoute->getAction('port'));
        $this->assertEquals('superv.io', $acpRoute->getDomain());
        $this->assertEquals('acp', $acpRoute->getPrefix());

        $apiRoute = $getRoutes['api.superv.ioapi/foo'];
        $this->assertEquals('api', $apiRoute->getAction('port'));
        $this->assertEquals('api.superv.io', $apiRoute->getDomain());
        $this->assertNull($apiRoute->getPrefix());
    }

    /**
     * @return \Illuminate\Routing\Router
     */
    protected function router()
    {
        return $this->app['router'];
    }
}