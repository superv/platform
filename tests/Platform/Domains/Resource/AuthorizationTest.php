<?php

namespace Tests\Platform\Domains\Resource;

class AuthorizationTest extends ResourceTestCase
{
    function test__non_authorized_users_can_not_list_resource_entries()
    {
        $user = $this->newUser(['allow' => null]);
        $categories = $this->blueprints()->categories();

        $this->getJsonUser($categories->router()->defaultList(), $user)
             ->assertStatus(403);
        $this->getJsonUser($categories->router()->defaultList().'/data', $user)
             ->assertStatus(403);

        $user->allow($categories->getChildIdentifier('actions', 'list'));

        $this->getJsonUser($categories->router()->defaultList(), $user)
             ->assertOk();
        $this->getJsonUser($categories->router()->defaultList().'/data', $user)
             ->assertOk();
    }

    function test__non_authorized_users_can_NOT_view_resource_dashboard()
    {
        $resource = $this->blueprints()->categories();

        $user = $this->newUser(['allow' => null]);
        $this->getJsonUser($resource->router()->dashboard(), $user)->assertStatus(403);

        $user->allow($resource->getChildIdentifier('actions', 'create'));
        $this->getJsonUser($resource->router()->dashboard(), $user)->assertOk();

        $user->forbid($resource->getChildIdentifier('actions', 'create'));
        $user->allow($resource->getChildIdentifier('actions', 'list'));
        $this->getJsonUser($resource->router()->dashboard(), $user)->assertOk();
    }

    function test__non_authorized_users_can_NOT_create_entry()
    {
        $user = $this->newUser(['allow' => 'sv.testing.categories.fields:*']);
        $resource = $this->blueprints()->categories();

        $this->getJsonUser($resource->router()->createForm(), $user)
             ->assertStatus(403);

        $this->postJsonUser($resource->router()->store(), ['title' => 'Books'], $user)
             ->assertStatus(403);

        $user->allow($resource->getChildIdentifier('actions', 'create'));
        $this->getJsonUser($resource->router()->createForm(), $user)
             ->assertOk();
        $this->postJsonUser($resource->router()->store(), ['title' => 'Books'], $user)
             ->assertOk();
    }

    function test__non_authorized_users_can_NOT_edit_entry()
    {
        $user = $this->newUser(['allow' => 'sv.testing.categories.fields:*']);
        $resource = $this->blueprints()->categories();
        $entry = $resource->fake();

        $this->getJsonUser($entry->router()->updateForm(), $user)
             ->assertStatus(403);
        $this->postJsonUser($entry->router()->update(), ['title' => 'Books'], $user)
             ->assertStatus(403);

        $user->allow($resource->getChildIdentifier('actions', 'list'));
        $list = $this->getListComponent($resource);
        $this->assertNull($list->getRowAction('edit'));

        $user->allow($resource->getChildIdentifier('actions', 'edit'));
        $this->getJsonUser($entry->router()->updateForm(), $user)
             ->assertOk();
        $this->postJsonUser($entry->router()->update(), ['title' => 'Books'], $user)
             ->assertOk();
        $list = $this->getListComponent($resource);
        $this->assertNotNull($list->getRowAction('edit'));
    }
}