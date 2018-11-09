<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
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

        $fieldType = $res->getFieldType('name');

        $this->assertInstanceOf(Text::class, $fieldType);
        $this->assertEquals(['max:255', 'required'], $fieldType->makeRules());
        $this->assertEquals('text', $fieldType->getType());
    }
}