<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;

class ResourceTest extends ResourceTestCase
{
    /** @test */
    function creates_anonymous_model_class_if_not_provided()
    {
        $resource = $this->makeResource('test_users');

        $entry = $resource->newResourceEntryInstance();

        $this->assertInstanceOf(ResourceEntryModel::class, $entry->getEntry());
        $this->assertEquals('test_users', $entry->getTable());
    }

    /** @test */
    function instantiates_entries_using_provided_model()
    {
        $resource =  $this->create('test_resource_models', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');

            $resource->model(TestResourceModel::class);
        });

        $entry = $resource->newResourceEntryInstance();
        $this->assertInstanceOf(TestResourceModel::class, $entry->getEntry());
        $this->assertInstanceOf(TestResourceModel::class, $resource->fake()->getEntry());
        $this->assertEquals('test_resource_models', $entry->getTable());
    }

    function test__count()
    {
        $res = $this->makeResource('t_items');
        $res->fake([], 3);

        $this->assertEquals(3, $res->count());

    }
}

class TestResourceModel extends ResourceEntryModel {
        protected $table = 'test_resource_models';
        public $timestamps = false;
}
