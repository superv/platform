<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BooleanTest extends ResourceTestCase
{
    /** @test */
    function type_boolean()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active');
        });
        $this->assertColumnExists($res->getHandle(), 'active');

        $field = $res->getField('active');
        $this->assertEquals('boolean', $field->getType());
    }
}