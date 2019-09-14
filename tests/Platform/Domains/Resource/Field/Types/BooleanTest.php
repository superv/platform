<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BooleanTest extends ResourceTestCase
{
    function test__type_boolean()
    {
        $res = $this->create('tmp_table', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active');
        });
        $this->assertColumnExists('tmp_table', 'active');

        $field = $res->getField('active');
        $this->assertEquals('boolean', $field->getFieldType());
    }
}
