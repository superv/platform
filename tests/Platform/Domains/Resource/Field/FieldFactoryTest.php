<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldFactoryTest extends ResourceTestCase
{
    /** @test */
    function make_field_from_field_entry()
    {
        $fieldEntry = new FieldModel(['name' => 'title', 'type' => 'text']);

        $field = FieldFactory::createFromEntry($fieldEntry);
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('title', $field->getName());
        $this->assertEquals('text', $field->getType());

        $fieldType = $field->fieldType();
        $fieldType->setAccessor(function ($value) { return str_slug($value); });
        $field->build();

        $field->setValue('SuperV Platform');
        $this->assertEquals('superv-platform', $field->getValue());
    }
}