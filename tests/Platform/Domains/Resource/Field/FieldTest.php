<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldObserver;
use SuperV\Platform\Domains\Resource\Field\FieldValue;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    /** @test */
    function instantiates()
    {
        $field = new Field('display_name', 'text');
        $this->assertNotNull($field->uuid());
        $this->assertInstanceOf(FieldValue::class, $field->value());

        $this->assertEquals([
            'type'  => 'text',
            'uuid'  => $field->uuid(),
            'name'  => 'display_name',
            'label' => 'Display Name',
        ], $field->compose());
    }

    /** @test */
    function observable_values()
    {
        $entry = new TestEntry();
        $field = new Field('name', 'text');

        $field->attach($entry);
        $field->setValue('Omar');
        $this->assertEquals('Omar', $entry->name);

        $field->detach($entry);
        $field->setValue('Hattab');
        $this->assertEquals('Omar', $entry->name);
    }
}

class TestEntry implements FieldObserver
{
    public $name;

    public function fieldValueUpdated(FieldValue $fieldValue)
    {
        $this->{$fieldValue->fieldName()} = $fieldValue->get();
    }
}