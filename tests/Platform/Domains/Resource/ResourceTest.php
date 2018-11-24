<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use Tests\Platform\Domains\Resource\Fixtures\TestResourceEntry;

class ResourceTest extends ResourceTestCase
{
    function test__creates_anonymous_model_class_if_not_provided()
    {
        $resource = $this->makeResource('t_users');

        $entry = $resource->newEntryInstance();

        $this->assertInstanceOf(ResourceEntry::class, $entry);
        $this->assertEquals('t_users', $entry->getHandle());
    }

    function test__instantiates_entries_using_provided_model()
    {
        $resource = $this->create('t_entries',
            function (Blueprint $table, ResourceBlueprint $resource) {
                $table->increments('id');

                $resource->model(TestResourceEntry::class);
            });

        $entry = $resource->newEntryInstance();
        $this->assertInstanceOf(TestResourceEntry::class, $entry);
        $this->assertInstanceOf(TestResourceEntry::class, $resource->fake());
        $this->assertEquals('t_entries', $entry->getTable());
    }

    function test__get_rules()
    {
        $users = $this->schema()->users();

        $this->assertEquals([
            'name'  => ['required'],
            'email' => ['unique:t_users,email,NULL,id', 'required'],
            'bio'   => ['string'],
            'group_id' => ['required'],
        ], $users->getRules());

        $user = $users->fake();

        $this->assertEquals(
            ['unique:t_users,email,'.$user->getId().',id', 'required'],
            $users->getRules($user)['email']
        );


    }

    function test__count()
    {
        $res = $this->makeResource('t_items');
        $res->fake([], 3);

        $this->assertEquals(3, $res->count());
    }
}
