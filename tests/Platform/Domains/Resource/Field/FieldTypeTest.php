<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Field\Types\BelongsTo;
use SuperV\Platform\Domains\Resource\Field\Types\Boolean;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTypeTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected function setUp()
    {
        parent::setUp();

        Schema::create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->titleColumn();
        });

        Schema::create('t_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role')->titleColumn();
        });

        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->titleColumn();
            $table->unsignedInteger('age');
            $table->decimal('height', 3, 2);
            $table->text('bio');
            $table->email('email');
            $table->date('birthday');
            $table->boolean('employed');

            $table->belongsTo('t_groups', 'group');
            $table->hasMany('t_posts', 'posts');
            $table->belongsToMany('t_roles', 'roles', 't_user_roles', 'user_id', 'role_id');
        });

        Schema::create('t_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->belongsToResource('t_users', 'user', 'user_id', 'post_id');
        });

        $this->resource = Resource::of('t_posts');
        $this->resource->build();
    }

    /** @test */
    function type_text()
    {
        $this->assertDatabaseTableHasColumn('t_users', 'name');
        $field = Resource::of('t_users')->getField('name');

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals('text', $field->getType());
    }

    /** @test */
    function type_number_integer()
    {
        $this->assertDatabaseTableHasColumn('t_users', 'age');
        $resource = Resource::of('t_users');

        $field = $resource->getField('age');
        $field->setResource($resource->loadFake(['age' => '10', 'group_id' => 1]));

        $this->assertInstanceOf(Number::class, $field);
        $this->assertEquals('number', $field->getType());

        $this->assertSame(10, $field->getValue());
    }

    /** @test */
    function type_number_decimal()
    {
        $this->assertDatabaseTableHasColumn('t_users', 'height');
        $resource = Resource::of('t_users');

        $field = $resource->getField('height');
        $field->setResource($resource->loadFake(['height' => '1.754234', 'group_id' => 1]));

        $this->assertInstanceOf(Number::class, $field);
        $this->assertEquals('number', $field->getType());
        $this->assertEquals('decimal', $field->getConfigValue('type'));
        $this->assertEquals(3, $field->getConfigValue('total'));
        $this->assertEquals(2, $field->getConfigValue('places'));

        $this->assertSame(1.75, $field->getValue());
    }

    /** @test */
    function type_boolean()
    {
        $this->assertDatabaseTableHasColumn('t_users', 'employed');
        $resource = Resource::of('t_users');

        $field = $resource->getField('employed');
        $field->setResource($resource->loadFake(['employed' => 1, 'group_id' => 1]));

        $this->assertInstanceOf(Boolean::class, $field);
        $this->assertEquals('boolean', $field->getType());

        $this->assertSame(true, $field->getValue());
    }

    /** @test */
    function type_belongs_to()
    {
        $this->assertDatabaseTableHasColumn('t_users', 'group_id');
        $resource = Resource::of('t_users');

        $field = $resource->getField('group');
        $field->setResource($resource->loadFake(['group_id' => 123]));

        $this->assertInstanceOf(BelongsTo::class, $field);
        $this->assertEquals('select', $field->getType());

        $this->assertEquals(123, $field->getValue());
    }


    function builds()
    {
        $userResource = ResourceFactory::make('t_users');
        $userResource->create(['id' => 3, 'name' => 'Ali']);
        $userResource->create(['id' => 5, 'name' => 'Veli']);

        $field = $this->resource->getField('user');
        $builded = $field->build();

        $this->assertEquals('select', $field->getType());

        $this->assertEquals([
            ['value' => 3, 'text' => 'Ali'],
            ['value' => 5, 'text' => 'Veli'],
        ], $builded->getConfigValue('options'));
    }

    /** @test */
    function accessor()
    {
        $posts = ResourceFactory::make('t_posts');
        $entry = $posts->create(['title' => 'Post A', 'user_id' => 9]);

        $posts->loadEntry($entry->getId())->build();

        $userField = $posts->getField('user');

        $this->assertEquals(9, $userField->getValue());
    }
}