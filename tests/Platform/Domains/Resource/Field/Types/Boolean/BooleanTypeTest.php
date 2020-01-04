<?php

namespace Tests\Platform\Domains\Resource\Field\Types\Boolean;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Builder\Builder;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BooleanTypeTest extends ResourceTestCase
{
    function test__blueprint()
    {
        $blueprint = Builder::blueprint('sv.testing.posts', function (
            \SuperV\Platform\Domains\Resource\Builder\Blueprint $resource
        ) {
            $resource->boolean('active');
        });

        $activeBlueprint = $blueprint->getField('active');
        $this->assertEquals('boolean', $activeBlueprint->getField()->getType());
    }

    function test__builder()
    {
        Builder::create('sv.testing.posts', function (\SuperV\Platform\Domains\Resource\Builder\Blueprint $resource) {
            $resource->boolean('active');
        });

        $posts = ResourceFactory::make('sv.testing.posts');

        $activeField = $posts->getField('active');
        $this->assertNotNull($activeField);
        $this->assertEquals('boolean', $activeField->getType());
    }

    function test__value()
    {
        $field = $this->makeField('active', 'boolean');

        $this->assertTrue($field->value()->set(1)->get());
        $this->assertTrue($field->value()->set('true')->get());
        $this->assertFalse($field->value()->set('false')->get());
        $this->assertFalse($field->value()->set(0)->get());
        $this->assertFalse($field->value()->set(null)->get());
    }

    function test__builder_v1()
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
