<?php

namespace Tests\Platform\Domains\Resource\Model;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceEntryTest extends ResourceTestCase
{
    function test__sleep_wakeup()
    {
        $users = $this->create('t_users',
            function (Blueprint $table, ResourceBlueprint $resource) {
                $resource->model(TestUser::class);
                $table->increments('id');
                $table->string('name');
            });

        $entry = new TestUser(['name' => 'Omar']);
        $resourceEntry = new ResourceEntry($entry);
        $resourceEntry->__sleep();
        $resourceEntry->__wakeup();
        $this->assertInstanceOf(TestUser::class, $resourceEntry->getEntry());

        $entry = TestUser::create(['name' => 'Omar']);
        $resourceEntry = new ResourceEntry($entry);
        $resourceEntry->__sleep();
        $resourceEntry->__wakeup();
        $this->assertInstanceOf(TestUser::class, $resourceEntry->getEntry());
        $this->assertEquals($entry->fresh(), $resourceEntry->getEntry());
    }
}

class TestUser extends Entry
{
    protected $table = 't_users';

    public $timestamps = false;
}