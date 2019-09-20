<?php

namespace Tests\Platform\Domains\Resource;

class ResourceRouterTest extends ResourceTestCase
{
    function test__route()
    {
        $resource = $this->blueprints()->categories();
        $router = $resource->router();

        $expected = sprintf(sv_route('sv::forms.show', [
            'identifier' => $resource->getIdentifier().'.forms.default',
        ]));
        $this->assertEquals($expected, $router->createForm());

        $expected = sprintf(sv_route('resource.table', [
            'resource' => $resource->getIdentifier(),
        ]));
        $this->assertEquals($expected, $router->defaultList());
    }
}
