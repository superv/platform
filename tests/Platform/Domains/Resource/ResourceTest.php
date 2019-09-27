<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Resource\Resource;

/**
 * Class ResourceTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class ResourceTest extends ResourceTestCase
{
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

}
