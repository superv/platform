<?php

namespace Tests\Platform\Support\Meta;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Support\Meta\Meta;
use SuperV\Platform\Support\Meta\Repository;
use Tests\Platform\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $allMetas;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $allItems;

    protected function setUp()
    {
        parent::setUp();

        $this->allMetas = ResourceFactory::make('sv_meta');
        $this->allItems = ResourceFactory::make('sv_meta_items');
    }

    function test__creates_db_record_single_level()
    {
        $meta = Meta::make(['name' => 'Omar', 'age' => 33]);
        ($repo = new Repository)->save($meta);

        $this->assertEquals(1, $this->allMetas->count());

        $metaEntry = $this->allMetas->first();
        $this->assertEquals(2, $metaEntry->items()->count());

        $this->assertArrayContains([
            'value' => 'Omar',
        ], $metaEntry->items()->where('key', 'name')->first()->toArray());

        $this->assertArrayContains([
            'value' => 33,
        ], $metaEntry->items()->where('key', 'age')->first()->toArray());
    }

    function test__creates_db_record_2_level()
    {
        $meta = Meta::make(['config' => ['rules' => ['min' => 10, 'max' => 99]]]);
        ($repo = new Repository)->save($meta);
        $this->assertNotNull($meta->id());

        $this->assertEquals(1, $this->allMetas->count());
        $metaEntry = $this->allMetas->first();
        $this->assertEquals(1, $metaEntry->items()->count());
        $this->assertEquals(4, $this->allItems->count());
    }

    function test_load()
    {
        $meta = Meta::make(['config' => ['rules' => ['min' => 10, 'max' => 99]]]);
        ($repo = new Repository)->save($meta);

        $fresh = $repo->load($meta->uuid());
        $this->assertNotNull($fresh->id());
        $this->assertEquals($meta->compose(), $fresh->compose());
    }

    function test__update()
    {
        $repo = new Repository;

        $meta = Meta::make(['2' => '4']);
        $repo->save($meta);

        $meta->set('3', '9');
        $repo->save($meta);
        $this->assertEquals(1, $this->allMetas->count());
        $this->assertEquals(2, $this->allItems->count());
    }

    function test_owner()
    {
        $meta = Meta::make()->setOwner($owner = new TestOwner);
        ($repo = new Repository)->save($meta);
        $meta = $this->allMetas->newQuery()->where('owner_type', 'test_owners')->where('owner_id', 123)->first();
        $this->assertNotNull($meta);

        $meta = Meta::make()->setOwner('meta_owners', 234);
        ($repo = new Repository)->save($meta);
        $meta = $this->allMetas->newQuery()->where('owner_type', 'meta_owners')->where('owner_id', 234)->first();
        $this->assertNotNull($meta);
    }

    function test_owner_loads_meta_from_relation()
    {
        $owners = $this->create('t_owners', function (Blueprint $table) {
            $table->increments('id');
            $table->morphOne('sv_meta', 'config', 'owner', Repository::class);
        });

        $owner = $owners->create()->fresh();

        /** @var Meta $meta */
        $meta = $owner->makeConfig([]);
        $this->assertInstanceOf(Meta::class, $meta);
        $this->assertEquals($owner, $meta->getOwnerEntry());

        (new Repository)->save($meta);
        /** @var Meta $configMeta */
        $configMeta = $owner->fresh()->getConfig();
        $this->assertInstanceOf(Meta::class, $configMeta);
        $this->assertEquals($owner, $configMeta->getOwnerEntry());

        $configMeta->set('type', 'morph');
        (new Repository)->save($configMeta);

        $freshConfigMeta = $owner->fresh()->getConfig();

        $this->assertInstanceOf(Meta::class, $freshConfigMeta);
        $this->assertEquals($owner, $freshConfigMeta->getOwnerEntry());

        $this->assertEquals(['type' => 'morph'], $freshConfigMeta->data());
    }
}

class TestOwner extends Entry
{
    public function getMorphClass()
    {
        return 'test_owners';
    }

    public function getKey()
    {
        return 123;
    }
}