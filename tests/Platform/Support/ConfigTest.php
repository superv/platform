<?php

namespace Tests\Platform\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Support\Config;
use Tests\Platform\TestCase;

class ConfigTest extends TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    function test__creates_database_record()
    {
        $resource = $this->create('test_owners', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');
            $table->string('title');

            $resource->model(TestConfigOwner::class);
        });

        $owner = $resource->fake();
        $owner->getConfig()
              ->set('price', 100)
              ->set('title', 'Goods');

        $this->assertEquals([
            'price' => 100,
            'title' => 'Goods',
        ], $owner->getConfig()->get());

        $owner = $resource->find($owner->id());
        $this->assertFalse($owner->wasRecentlyCreated);

        $this->assertEquals([
            'price' => 100,
            'title' => 'Goods',
        ], $owner->getConfig()->get());


    }
}

class TestConfigOwner extends ResourceEntryModel
{
    public $timestamps = false;

    protected $table = 'test_owners';
}

