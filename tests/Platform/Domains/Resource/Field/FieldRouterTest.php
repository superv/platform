<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Router;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\GeniusType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldRouterTest extends ResourceTestCase
{
    function test__()
    {
        $router = $this->makeField('foo', GeniusType::class)->router();
        $this->assertInstanceOf(Router::class, $router);

        $this->assertEquals(sv_route('sv::fields.types', ['type'  => 'genius',
                                                          'route' => 'lookup']), $router->route('lookup'));
    }
}