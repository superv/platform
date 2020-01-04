<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\SchemaService;
use SuperV\Platform\Domains\Resource\Builder\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class DateTimeFieldTest extends ResourceTestCase
{
    function test__builder()
    {
        Builder::create('sv.testing.flights', function (Blueprint $resource) {
            $resource->datetime('arrived_at');
            $resource->datetime('cancelled_at')->nullable();
        });

        $this->assertFalse(SchemaService::resolve()->getColumn('flights', 'arrived_at')->isNullable());
        $this->assertTrue(SchemaService::resolve()->getColumn('flights', 'cancelled_at')->isNullable());
    }
}