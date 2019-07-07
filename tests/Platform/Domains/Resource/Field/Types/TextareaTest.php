<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TextareaTest extends ResourceTestCase
{
    function test__type_textarea()
    {
        $res = $this->create(function (Blueprint $table) {
            $table->increments('id');
            $table->text('bio');
        });
        $this->assertColumnExists($res->getHandle(), 'bio');

        $field = $res->getField('bio');

        $this->assertEquals('textarea', $field->getFieldType());
    }
}