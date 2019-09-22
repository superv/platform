<?php

namespace Tests\Platform\Domains\Resource;

class ResourceRouterTest extends ResourceTestCase
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Resource\Router
     */
    protected $router;

    protected function setUp()
    {
        parent::setUp();

        $this->resource = $this->blueprints()->categories();
        $this->router = $this->resource->router();
    }

    function test__create_form()
    {
        $expected = sprintf(sv_route('sv::forms.show', [
            'identifier' => $this->resource->getIdentifier().'.forms.default',
        ]));
        $this->assertEquals($expected, $this->router->createForm());
    }

    function test__default_list()
    {
        $expected = sprintf(sv_route('resource.table', [
            'resource' => $this->resource->getIdentifier(),
        ]));
        $this->assertEquals($expected, $this->router->defaultList());
    }

    function test__dashboard_page()
    {
        $expected = sprintf(sv_route('resource.dashboard', [
            'resource' => $this->resource->getIdentifier(),
        ]));
        $this->assertEquals($expected, $this->router->dashboard());
    }
}
