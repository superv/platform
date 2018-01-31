<?php

namespace Tests\SuperV\Platform\Domains\Routing;

use SuperV\Platform\Domains\Routing\RouteLoader;
use Tests\SuperV\Platform\BaseTestCase;

class RouteLoaderTest extends BaseTestCase
{
    /**
     * @test
     */
    function register_routes_from_array()
    {
        $loader = $this->app->make(RouteLoader::class);

        $loader->fromArray([
            'web/foo'       => 'WebController@foo',
            'web/bar'       => [
                'uses' => 'WebController@bar',
                'as'   => 'web.bar',
            ],
            'post@web/foo'  => 'WebController@postFoo',
            'patch@web/bar' => function () { },
        ]);

        $getRoutes = $this->app['router']->getRoutes()->get('GET');

        $this->assertEquals('WebController@foo', $getRoutes['web/foo']->getAction('controller'));
        $this->assertEquals('WebController@bar', $getRoutes['web/bar']->getAction('controller'));
        $this->assertEquals('web.bar', $getRoutes['web/bar']->getName());

        $postRoutes = $this->app['router']->getRoutes()->get('POST');
        $this->assertEquals('WebController@postFoo', $postRoutes['web/foo']->getAction('controller'));

        $patchRoutes = $this->app['router']->getRoutes()->get('PATCH');
        $this->assertInstanceOf(\Closure::class, $patchRoutes['web/bar']->getAction('uses'));
    }
}