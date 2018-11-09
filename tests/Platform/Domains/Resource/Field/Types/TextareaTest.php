<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Textarea;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TextareaTest extends ResourceTestCase
{
    /** @test */
    function type_textarea()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->text('bio');
        });
        $this->assertColumnExists($res->getHandle(), 'bio');

        $field = $res->freshWithFake()->build()->getFieldType('bio');

        $this->assertInstanceOf(Textarea::class, $field);
        $this->assertEquals('textarea', $field->getType());

        $this->assertEquals(true, $field->getConfigValue('hide.table'));
    }
}