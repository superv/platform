<?php

namespace Tests\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Field\Types\Select;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BelongsToFieldTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @test */
    function creates_field()
    {
        $this->assertEquals(['id', 'title', 'user_id'], \Schema::getColumnListing('test_posts'));

        $userField = $this->resource->getField('user');
        $this->assertNotNull($userField);
        $this->assertEquals('select', $userField->getType());

        $this->assertEquals([
            'related_resource' => 'test_users',
            'foreign_key'      => 'user_id',
            'owner_key'        => 'post_id',
        ], $userField->getConfig());
    }

    /** @test */
    function builds()
    {
        $userResource = ResourceFactory::make('test_users');
        $userResource->create(['id' => 3, 'full_name' => 'Ali']);
        $userResource->create(['id' => 5, 'full_name' => 'Veli']);

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
        $posts = ResourceFactory::make('test_posts');
        $entry = $posts->create(['title' => 'Post A', 'user_id' => 9]);

        $posts->loadEntry($entry->getId())->build();

        $userField = $posts->getField('user');

        $this->assertEquals(9, $userField->getValue());
    }

    protected function setUp()
    {
        parent::setUp();

        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name')->titleColumn();
        });

        Schema::create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->belongsToResource('test_users', 'user', 'user_id', 'post_id');
        });

        $this->resource = ResourceFactory::make('test_posts');
        $this->resource->build();
    }
}