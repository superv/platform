<?php

namespace Tests\Platform\Domains\Resource;

use Exception;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\Fixtures\TestUser;

class ResourceCreationTest extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();
//        $this->app['migrator']->run(__DIR__.'/migrations');
    }

    /** @test */
    function creates_resource_model_entry_when_a_table_is_created()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
        });

        $this->assertDatabaseHas('sv_resources', ['slug' => 'test_users']);
        $resource = ResourceModel::withSlug('test_users');
        $this->assertNotNull($resource);
        $this->assertNotNull($resource->uuid());
        $this->assertEquals('platform', $resource->getAddon());
    }

    /** @test */
    function saves_resource_model_class_if_provided()
    {
        Schema::create('test_users', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');
            $resource->model(TestUser::class);
        });

        $this->assertEquals(TestUser::class, ResourceModel::withSlug('test_users')->getModelClass());
        $this->assertInstanceOf(TestUser::class, Resource::of('test_users')->resolveModel());
    }

    /** @test */
    function creates_field_when_a_database_column_is_created()
    {
        $resource = $this->makeResourceModel('test_users', ['name', 'age:integer', 'bio:text']);
        $this->assertEquals(3, $resource->fields()->count());

        $nameField = $resource->getField('name');
        $this->assertEquals('string', $nameField->getColumnType());
        $this->assertNotNull($nameField->uuid());

        $ageField = $resource->getField('age');
        $this->assertEquals('integer', $ageField->getColumnType());
        $this->assertNotNull($ageField->uuid());
    }

    /** @test */
    function fields_are_unique_per_resource()
    {
        $resourceEntry = $this->makeResourceModel('test_users', ['name']);
        $this->assertEquals(1, $resourceEntry->fields()->count());

        $this->expectException(Exception::class);
        $resourceEntry->createField('name');
    }

    /** @test */
    function saves_field_rules()
    {
        $resource = $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->rules(['min:6', 'max:32']);
            $table->string('email')->rules('email|unique');
        });

        $this->assertArrayContains(['min:6', 'max:32'], $resource->getField('name')->getRules());
        $this->assertArrayContains(['email', 'unique'], $resource->getField('email')->getRules());
    }

    /** @test */
    function saves_field_type()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->select('status')->options(['closed' => 'Closed', 'open' => 'Open'])->default('open');
        });

        $resource = ResourceModel::withSlug('test_users');

        $statusField = $resource->getField('status');
        $this->assertEquals('string', $statusField->getColumnType());
        $this->assertEquals('select', $statusField->getType());
        $this->assertEquals(['closed' => 'Closed', 'open' => 'Open'], $statusField->getConfigValue('options'));
    }

    /** @test */
    function updates_field_rules()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->rules(['min:6', 'max:32']);
            $table->string('email')->rules('email|unique');
        });

        Schema::table('test_users', function (Blueprint $table) {
            $table->string('name')->change()->rules(['min:16', 'max:64']);
        });
        $resource = ResourceModel::withSlug('test_users');
        $nameField = $resource->getField('name');

        $this->assertArrayContains(['min:16', 'max:64'], $nameField->getRules());
    }

    /** @test */
    function does_not_create_fields_for_timestamp_columns()
    {
        $resource = $this->makeResourceModel('test_users', ['name', 'timestamps']);

        $this->assertEquals(1, $resource->fields()->count());
    }

    /** @test */
    function deletes_field_when_a_column_is_dropped()
    {
        $resource = $this->makeResourceModel('test_users', ['name', 'title']);

        $this->assertNotNull($resource->getField('name'));
        $this->assertNotNull($resource->getField('title'));

        Schema::table('test_users', function (Blueprint $table) {
            $table->dropColumn(['title', 'name']);
        });
        $resource->load('fields');
        $this->assertNull($resource->getField('name'));
        $this->assertNull($resource->getField('title'));
    }

    /** @test */
    function deletes_fields_when_a_resource_is_deleted()
    {
        $resource = $this->makeResourceModel('test_users', ['name', 'title']);

        $this->assertEquals(2, $resource->fields()->count());

        $resource->delete();

        $this->assertEquals(0, $resource->fields()->count());
    }

    /** @test */
    function marks_required_columns()
    {
        $resource = $this->makeResourceModel('test_users', ['name', 'title' => 'nullable']);

        $this->assertTrue($resource->getField('name')->isRequired());
        $this->assertTrue($resource->getField('title')->isNullable());
    }

    /** @test */
    function marks_unique_columns()
    {
        $resource = $this->makeResource('test_users', ['name', 'slug' => 'unique'])->build();

        $slugField = $resource->getField('slug');
        $this->assertTrue($slugField->isUnique());

        $this->assertArraySubset(['unique:test_users,slug,{entry.id},id'], $slugField->getRules());
    }

    /** @test */
    function marks_searchable_columns()
    {
        $resource = $this->makeResourceModel('test_users', ['name', 'title' => 'searchable']);

        $this->assertTrue($resource->getField('title')->isSearchable());
    }

    /** @test */
    function save_column_default_value()
    {
        Schema::create('test_users', function (Blueprint $table) {
            $table->string('title')->default('User');
        });

        $resource = ResourceModel::withSlug('test_users');
        $this->assertEquals('User', $resource->getField('title')->getDefaultValue());
    }
}
