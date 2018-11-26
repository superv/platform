<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class NumberTest extends ResourceTestCase
{
    function test_type_number_integer()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('age');
        });
        $this->assertColumnExists($res->getHandle(), 'age');

        $age = $res->getField('age');

        $this->assertEquals('number', $age->getType());
        $this->assertEquals(['integer', 'min:0', 'required'], $res->parseFieldRules('age'));
        $this->assertEquals('integer', $age->getConfigValue('type'));
        $this->assertTrue($age->getConfigValue('unsigned'));
    }

    function test_type_number_decimal()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('height', 3, 2);
        });
        $this->assertColumnExists($res->getHandle(), 'height');

        $height = $res->getField('height');

        $this->assertEquals('number', $height->getType());
        $this->assertEquals('decimal', $height->getConfigValue('type'));
        $this->assertEquals(['numeric', 'required'], $res->parseFieldRules('height'));

        $this->assertEquals(3, $height->getConfigValue('total'));
        $this->assertEquals(2, $height->getConfigValue('places'));
//        $this->assertSame(1.75, $height->getValue());
    }
}