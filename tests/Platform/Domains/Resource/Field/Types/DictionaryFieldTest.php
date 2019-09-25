<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;

class DictionaryFieldTest
{
    function test__()
    {
        $res = $this->create('tmp_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->dictionary('config');
        });
        $this->assertColumnExists('tmp_table', 'config');

        $field = $res->getField('config');
        $this->assertEquals('dictionary', $field->getFieldType());

        $entry = $this->postCreateResource($res, ['title' => 'test-title', 'config' => ['foo' => 'bar']]);

        $this->assertEquals('test-title', $entry->title);
        $this->assertEquals(['foo' => 'bar'], $entry->config);
//        $this->field->getIdentifier() === 'testing.tmp_table.fields:config'
    }
}
