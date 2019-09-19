<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TextTest extends ResourceTestCase
{
    function test__type_text()
    {
        $res = $this->create('tmp_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        $this->assertColumnExists('tmp_table', 'name');

        $field = $res->getField('name');

        $this->assertEquals('text', $field->getFieldType());
        $this->assertEquals(['max:255', 'required'], $res->parseFieldRules('name'));
        $this->assertEquals('text', $field->getFieldType());
    }
}
