<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FieldFactoryTest
 *
 * @package Tests\Platform\Domains\Resource\Field
 * @group   resource
 */
class FieldFactoryTest extends ResourceTestCase
{
    function test__make_field_from_field_entry()
    {
        $fieldEntry = new FieldModel(['identifier' => uuid(), 'name' => 'title', 'type' => 'text']);

        $field = FieldFactory::createFromEntry($fieldEntry);
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('title', $field->getName());
        $this->assertEquals('text', $field->getFieldType());

//        $fieldType = $field->fieldType();
//        $fieldType->setAccessor(function ($value) { return str_slug($value); });
//
//        $field->setValue('SuperV Platform');
//        $this->assertEquals('superv-platform', $field->getValue());
    }
}
