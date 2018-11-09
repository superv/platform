<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Boolean;
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

        $fieldType = $res->fake(['active' => 1])->getFieldType('active');

        $this->assertInstanceOf(Boolean::class, $fieldType);
        $this->assertEquals('boolean', $fieldType->getType());

//        $this->assertSame(true, $fieldType->getValue());
    }
}