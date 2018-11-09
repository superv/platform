<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TextTest extends ResourceTestCase
{
    /** @test */
    function type_text()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        $this->assertColumnExists($res->getHandle(), 'name');

        $field = $res->freshWithFake()->build()->getFieldType('name');

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals(['max:255', 'required'], $field->makeRules());
        $this->assertEquals('text', $field->getType());
    }
}