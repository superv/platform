<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use Tests\Platform\Domains\Resource\Fixtures\TestResourceEntry;

/**
 * Class ResourceTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ResourceTest extends ResourceTestCase
{
    function test__creates_anonymous_model_class_if_not_provided()
    {
        $resource = $this->makeResource('tbl_users');

        $entry = $resource->newEntryInstance();

        $this->assertInstanceOf(ResourceEntry::class, $entry);
        $this->assertEquals('tbl_users', $entry->getTable());
    }

    function test__instantiates_entries_using_provided_model()
    {
        $resource = $this->create('t_entries',
            function (Blueprint $table, ResourceConfig $resource) {
                $table->increments('id');

                $resource->model(TestResourceEntry::class);
            });

        $entry = $resource->newEntryInstance();
        $this->assertInstanceOf(TestResourceEntry::class, $entry);
        $this->assertInstanceOf(TestResourceEntry::class, $resource->fake());
        $this->assertEquals('t_entries', $entry->getTable());
    }

    function test__get_creation_rules()
    {
        $users = $this->blueprints()->users();

        $this->assertEquals([
            'name'     => ['max:255', 'required'],
            'email'    => ['unique:tbl_users,email,NULL,id', 'required'],
            'bio'      => ['max:255', 'string', 'nullable'],
            'group_id' => ['required'],
            'age'      => ['integer', 'min:0', 'nullable'],
            'avatar'   => ['nullable'],
            'roles'    => ['nullable'],
        ], $users->getRules());
    }

    function test__get_update_rules()
    {
        $users = $this->blueprints()->users();

        $user = $users->fake();

        $this->assertEquals([
            'name'     => ['max:255', 'sometimes', 'required'],
            'email'    => ['unique:tbl_users,email,'.$user->getId().',id', 'sometimes', 'required'],
            'bio'      => ['max:255', 'string', 'nullable'],
            'group_id' => ['sometimes', 'required'],
            'age'      => ['integer', 'min:0', 'nullable'],
            'avatar'   => ['nullable'],
            'roles'    => ['nullable'],
        ], $users->getRules($user));
    }

    function test__rules_for_dynamically_added_fields()
    {
        $this->blueprints()->users();
        Resource::extend('testing.users')
                ->with(function (Resource $resource) {
                    $resource->indexFields()->add(['type' => 'text', 'name' => 'isot']);
                });

        $users = sv_resource('testing.users');

        $this->assertFalse(in_array('isot', array_keys($users->getRules())));
    }

    function test__count()
    {
        $res = $this->makeResource('t_items');
        $res->fake([], 3);

        $this->assertEquals(3, $res->count());
    }
}
