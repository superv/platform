<?php

namespace Tests\Platform\Domains\Resource;

class ResourceRouterTest extends ResourceTestCase
{
    function test__route()
    {
        $resource = $this->blueprints()->categories();

        $router = $resource->router();

        $expected = sprintf(sv_route('sv::forms.show', [
            'namespace' => $resource->getIdentifier(),
            'name'      => 'default',
        ]));

        $this->assertEquals($expected, $router->createForm());
    }
}
