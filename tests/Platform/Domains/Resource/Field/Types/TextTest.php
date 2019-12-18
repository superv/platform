<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\Field\FieldRules;
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
        $this->assertEquals(['max:255', 'required'], (new FieldRules($field))->get());
        $this->assertEquals('text', $field->getFieldType());
    }

    function test__blueprint()
    {
        $blueprint = Builder::blueprint('testing.posts', function (
            \SuperV\Platform\Domains\Resource\Builder\Blueprint $resource
        ) {
            $resource->text('title')->useAsEntryLabel();
        });

        $titleBlueprint = $blueprint->getField('title');
        $this->assertTrue($titleBlueprint->isEntryLabel());
        $this->assertEquals('text', $titleBlueprint->getField()->getType());
    }
}
