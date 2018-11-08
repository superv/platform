<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldConfig;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use SuperV\Platform\Domains\Resource\Field\Types\Select;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected function setUp()
    {
        parent::setUp();

        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->rules(['min:3']);
            $table->email('email')->unique();
            $table->string('gender');
            $table->unsignedInteger('age')->nullable();
        });

        $this->resource = ResourceFactory::make('test_users');
        $this->resource->build();
    }

    /** @test */
    function builds_from_string()
    {
        $builder = new FieldFactory($this->resource);
        $field = $builder->make('name');

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals('name', $field->getName());
        $this->assertEquals('text', $field->getType());
//        $this->assertEquals($this->resource, $field->getResource());
    }

    /** @test */
    function builds_from_field_entry()
    {
        $fieldEntry = ResourceModel::withSlug('test_users')->getField('name');

        $builder = new FieldFactory($this->resource);
        $field = $builder->make($fieldEntry);

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals('name', $field->getName());
        $this->assertEquals('text', $field->getType());
//        $this->assertEquals($this->resource, $field->getResource());
    }

    /** @test */
    function builds_from_instance()
    {
        $builder = new FieldFactory($this->resource);
        $field = $builder->make(Select::make('gender'));

        $this->assertInstanceOf(Select::class, $field);
        $this->assertEquals('gender', $field->getName());
        $this->assertEquals('select', $field->getType());
//        $this->assertEquals($this->resource, $field->getResource());
    }

    /** @test */
    function builds_from_config()
    {
        $builder = new FieldFactory($this->resource);
        $field = $builder->make(
            FieldConfig::field('name')
                       ->mergeRules(['min:10'])
                       ->config(['foo' => 'bar'])
        );

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals(['max:255', 'min:10', 'required'], $field->makeRules());
        $this->assertEquals(['foo' => 'bar', 'length' => 255], $field->getConfig());
//        $this->assertEquals($this->resource, $field->getResource());

        $builder = new FieldFactory($this->resource);
        $age = $builder->make(
            FieldConfig::field('age')
                       ->mergeRules(['min:18'])
        );

        $this->assertInstanceOf(Number::class, $age);
        $this->assertEquals(['integer', 'min:18', 'nullable'], $age->makeRules());
    }

    /** @test */
    function composes_field()
    {
        $field = new TestField(new FieldModel(['uuid' => 'abc-123']));
        $field->setConfig(['test-config']);

        $this->assertEquals([
            'uuid'   => 'abc-123',
            'name'   => 'test-name',
            'label'  => $field->getLabel(),
            'type'   => 'test-type',
            'config' => ['test-config'],
        ], $field->build()->compose());
    }
}

class TestField extends Field
{
    protected $label = 'test-label';

    protected $type = 'test-type';

    protected $name = 'test-name';
}