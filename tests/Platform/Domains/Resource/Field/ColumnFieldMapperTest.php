<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\ColumnFieldMapper as Mapper;
use SuperV\Platform\Testing\PlatformTestCase;

/**
 * Class ColumnFieldMapperTest
 *
 * @package Tests\Platform\Domains\Resource\Field
 * @group   resource
 */
class ColumnFieldMapperTest extends PlatformTestCase
{
    function test__maps_string_column_to_text_field()
    {
        $mapper = Mapper::for('string')->map(['length' => 64]);

        $this->assertEquals('text', $mapper->getFieldType());
        $this->assertEquals(64, $mapper->getConfigValue('length'));
    }

    function test__maps_text_columns_to_textarea_field()
    {
        $mapper = Mapper::for('text')->map();
        $this->assertEquals('textarea', $mapper->getFieldType());
        $this->assertEquals([], $mapper->getRules());

        $mapper = Mapper::for('mediumText')->map();
        $this->assertEquals('textarea', $mapper->getFieldType());

        $mapper = Mapper::for('longText')->map();
        $this->assertEquals('textarea', $mapper->getFieldType());
    }

    function test__maps_integer_columns_to_number_field()
    {
        $mapper = Mapper::for('integer')->map();
        $this->assertEquals('number', $mapper->getFieldType());
//        $this->assertEquals(['integer'], $mapper->getRules());
        $this->assertEquals('integer', $mapper->getConfigValue('type'));

        $mapper = Mapper::for('integer')->map(['unsigned' => true]);
        $this->assertEquals('number', $mapper->getFieldType());
//        $this->assertArrayContains(['integer', 'min:0'], $mapper->getRules());

        $mapper = Mapper::for('bigInteger')->map();
        $this->assertEquals('number', $mapper->getFieldType());

        $mapper = Mapper::for('tinyInteger')->map();
        $this->assertEquals('number', $mapper->getFieldType());
    }

    function test__maps_decimal_columns_to_number_field()
    {
        $mapper = Mapper::for('decimal')->map(['total' => 12, 'places' => 8]);
        $this->assertEquals('number', $mapper->getFieldType());
        $this->assertEquals('decimal', $mapper->getConfigValue('type'));
        $this->assertEquals(12, $mapper->getConfigValue('total'));
        $this->assertEquals(8, $mapper->getConfigValue('places'));
    }

    function test__maps_boolean_column_to_boolean_field()
    {
        $mapper = Mapper::for('boolean')->map();
        $this->assertEquals('boolean', $mapper->getFieldType());
    }

    function test__maps_enum_column_to_select_field()
    {
        $mapper = Mapper::for('enum')->map(['allowed' => ['foo', 'bar']]);
        $this->assertEquals('select', $mapper->getFieldType());
        $this->assertEquals(['foo', 'bar'], $mapper->getConfigValue('options'));
    }

    function test__maps_uuid_column_to_text_field()
    {
        $mapper = Mapper::for('uuid')->map();
        $this->assertEquals('text', $mapper->getFieldType());
    }

    function test__maps_date_column_to_datetime_field()
    {
        $mapper = Mapper::for('date')->map();
        $this->assertEquals('datetime', $mapper->getFieldType());
        $this->assertEquals(false, $mapper->getConfigValue('time'));
    }

    function test__maps_datetime_column_to_datetime_field()
    {
        $mapper = Mapper::for('datetime')->map();
        $this->assertEquals('datetime', $mapper->getFieldType());
        $this->assertEquals(true, $mapper->getConfigValue('time'));
    }
}