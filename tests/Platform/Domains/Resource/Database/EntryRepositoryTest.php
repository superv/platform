<?php

namespace Tests\Platform\Domains\Resource\Database;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Database\Entry\AnonymousModel;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\Fixtures\TestResourceEntry;
use Tests\Platform\Domains\Resource\Fixtures\TestUser;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class EntryRepositoryTest extends ResourceTestCase
{
    function test__creates_anonymous_model_class_if_not_provided()
    {
        $resource = $this->makeResource('tbl_users');

        $entry = $resource->newEntryInstance();
        $this->assertInstanceOf(ResourceEntry::class, $entry);
        $this->assertEquals('tbl_users', $entry->getTable());

//        $entry = EntryRepository::resolve()->newEntryInstance($resource);
        $entry = EntryRepository::for($resource)->newInstance();

        $this->assertInstanceOf(ResourceEntry::class, $entry);
        $this->assertEquals('tbl_users', $entry->getTable());
    }

    function test__dynamic_relations_on_anonymous_models()
    {
        $users = $this->blueprints()->users();
        $entry = EntryRepository::for($users)->newInstance();

        $this->assertInstanceOf(AnonymousModel::class, $entry);

        $this->assertEquals($users->getRelations()->keys()->all(), $entry->getRelationKeys());
        $this->assertInstanceOf(BelongsTo::class, $entry->group());
    }

    function test__dynamic_relations_on_custom_models()
    {
        $this->blueprints()->comments();

        $users = $this->blueprints()->users(function (Blueprint $table, ResourceConfig $config) {
            $config->model(TestUser::class);
        });
        $entry = sv_resource('sv.testing.users')->create(['name' => 'aaa', 'email' => 'a@h.com', 'group_id' => 1]);
        $this->assertInstanceOf(TestUser::class, $entry);
        $this->assertEquals($users->getRelations()->keys()->all(), $entry->getRelationKeys());
        $this->assertInstanceOf(BelongsTo::class, $entry->group());
        $this->assertInstanceOf(HasMany::class, $entry->comments());

        $entry = TestUser::create(['name' => 'aaa', 'email' => 'a2@h.com', 'group_id' => 1]);
        $this->assertInstanceOf(BelongsTo::class, $entry->group());
        $this->assertInstanceOf(HasMany::class, $entry->comments());
    }

    function test__instantiates_entries_using_provided_model()
    {
        $resource = $this->create('t_entries',
            function (Blueprint $table, ResourceConfig $config) {
                $table->increments('id');

                $config->model(TestResourceEntry::class);
            });

        $entry = EntryRepository::for($resource)->newInstance();
        $this->assertInstanceOf(TestResourceEntry::class, $entry);
        $this->assertInstanceOf(TestResourceEntry::class, $resource->fake());
        $this->assertEquals('t_entries', $entry->getTable());
    }

    function test__count()
    {
        $res = $this->makeResource('t_items');
        $res->fake([], 3);

        $this->assertEquals(3, EntryRepository::for($res)->count());
    }
}