<?php

namespace Tests\Platform\Domains\Resource\Field\Types\Number;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldRules;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class NumberTest extends ResourceTestCase
{
    function test_type_number_integer()
    {
        $res = $this->create('tmp_table', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('age');
        });
        $this->assertColumnExists('tmp_table', 'age');

        $age = $res->getField('age');

        $this->assertEquals('number', $age->getFieldType());
        $this->assertEquals(['integer', 'min:0', 'max:4294967295', 'required'], (new FieldRules($age))->get());
        $this->assertEquals('integer', $age->getConfigValue('type'));
        $this->assertTrue($age->getConfigValue('unsigned'));
    }

    function test_type_number_decimal()
    {
        $res = $this->create('tmp_table', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('height', 3, 2);
        });
        $this->assertColumnExists('tmp_table', 'height');

        $height = $res->getField('height');

        $this->assertEquals('number', $height->getFieldType());
        $this->assertEquals('decimal', $height->getConfigValue('type'));
        $this->assertEquals(['numeric', 'max:9.99', 'required'], (new FieldRules($height))->get());

        $this->assertEquals(3, $height->getConfigValue('total'));
        $this->assertEquals(2, $height->getConfigValue('places'));
//        $this->assertSame(1.75, $height->getValue());
    }
}
