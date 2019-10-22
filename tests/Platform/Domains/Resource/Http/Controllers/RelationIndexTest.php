<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Testing\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class RelationIndexTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 * @group   http
 */
class RelationIndexTest extends ResourceTestCase
{
    function test__index_listing_with_has_many_relations()
    {
        $users = $this->blueprints()->users();
        $posts = $this->blueprints()->posts();

        // seed the main user (parent)
        //
        $userA = $users->fake();
        $userPosts = $userA->posts()->createMany($posts->fakeMake([], 5));

        // seed some other data, that should not be displayed
        //
        $userB = $users->fake();
        $userB->posts()->createMany($posts->fakeMake([], 3));

        // make sure we have 8 posts in total
        //
        $this->assertEquals(8, $posts->count());

        // get the relation table over http
        //
        $this->withoutExceptionHandling();

        $url = route('relation.index', ['resource' => 'testing.users',
                                        'entry'    => $userA->getId(),
                                        'relation' => 'posts']);
        $response = $this->getJsonUser($url);
        $response->assertOk();
        $table = HelperComponent::fromArray($response->decodeResponseJson('data'));

        // We should have 1 context action (Create New)
        //
        $this->assertEquals(1, count($table->getProp('config.context_actions')));
        $action = HelperComponent::fromArray($table->getProp('config.context_actions.0'));

        // Button title should be generated from, singular relation name
        //
        $this->assertEquals('New Post', $action->getProp('title'));

        // Check the actions url, should point to create new relation form
        //
        $this->assertEquals(
            sv_route('relation.create', ['resource' => 'testing.users',
                                         'entry'    => $userA->getId(),
                                         'relation' => 'posts']),
            sv_url($action->getProp('url')));

        // Now get the table data
        //
        $response = $this->getJsonUser($table->getProp('config.data_url'));
        $response->assertOk();
        $rows = $response->decodeResponseJson('data.rows');

        // check row count
        //
        $this->assertEquals(5, count($rows));

        // check the View Action Url
        //
        $viewAction = HelperComponent::fromArray($table->getProp('config.row_actions.0'));
        $firstPost = $userPosts->first();
        $actual = str_replace('{entry.id}', $firstPost->getId(), $viewAction->getProp('url'));
        $this->assertEquals($firstPost->router()->dashboard('edit'), sv_url($actual));
    }

    function test__index_listing_with_belongs_to_many_relations()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()->users();

        $userA = $users->fake();
        $userA->roles()->attach([1 => ['status' => 'note-1']]);
        $userA->roles()->attach([2 => ['status' => 'note-2']]);

        $relation = $users->getRelation('roles', $userA);

        $url = $relation->route('index', $userA);
        $response = $this->getJsonUser($url);
        $response->assertOk();

        $table = HelperComponent::fromArray($response->decodeResponseJson('data'));

        //  Only two fields for this table,
        //  role.title + pivot.status
        //
        $fields = $table->getProp('config.fields');
        $this->assertEquals(2, count($fields));

        // Check row action VIEW & DETACH
        //
        $this->assertEquals(2, count($table->getProp('config.row_actions')));
        $action = HelperComponent::fromArray($table->getProp('config.row_actions.1'));

        $this->assertEquals('sv/res/testing.roles/{entry.id}/view', $action->getProp('url'));

        // Check context action ATTACH NEW
        //
        $this->assertEquals(1, count($table->getProp('config.context_actions')));
        $action = HelperComponent::fromArray($table->getProp('config.context_actions.0'));

        $this->assertEquals('sv-attach-entry-action', $action->getName());
        $this->assertEquals(sv_url($relation->route('lookup', $userA)), $action->getProp('lookup-url'));
        $this->assertEquals(sv_url($relation->route('attach', $userA)), $action->getProp('attach-url'));

        $this->assertEquals('status', $action->getProp('pivot-fields.status.name'));

        $response = $this->getJsonUser($table->getProp('config.data_url'));
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(2, count($rows));

        // first make sure we have the pivot fields on table
        //
        $this->assertNotNull($rows[0]['fields'][1] ?? null);

        $this->assertEquals('note-1', $rows[1]['fields'][1]['value']);
        $this->assertEquals('note-2', $rows[0]['fields'][1]['value']);
    }

    function test__index_listing_with_morph_to_many_relations()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()->users();
        $this->blueprints()->actions();

        $userA = $users->fake();

        $userA->actions()->attach([1 => ['provision' => 'pass']]);
        $userA->actions()->attach([2 => ['provision' => 'fail']]);
        $userA->actions()->attach([3 => ['provision' => 'fail']]);

        $relation = $users->getRelation('actions', $userA);

        $url = $relation->route('index', $userA);
        $response = $this->getJsonUser($url);
        $response->assertOk();

        $table = HelperComponent::fromArray($response->decodeResponseJson('data'));

        //  Only two fields for this table,
        //  action.slug + pivot.provision
        //
        $fields = $table->getProp('config.fields');
        $this->assertEquals(2, count($fields));

        // We should have 1 context action (Attach)
        //
        $this->assertEquals(1, count($table->getProp('config.context_actions')));
        $action = HelperComponent::fromArray($table->getProp('config.context_actions.0'));

        $this->assertEquals('sv-attach-entry-action', $action->getName());
        $this->assertEquals(sv_url($relation->route('lookup', $userA)), $action->getProp('lookup-url'));
        $this->assertEquals(sv_url($relation->route('attach', $userA)), $action->getProp('attach-url'));
        $this->assertEquals('provision', $action->getProp('pivot-fields.provision.name'));

        $response = $this->getJsonUser($table->getProp('config.data_url'));
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        // to avoid race condition
        $rows = collect($rows)->sortBy('id')->values()->all();

        $this->assertEquals(3, count($rows));

        // first make sure we have the pivot fields on table
        //
        $this->assertNotNull($rows[0]['fields'][1] ?? null);

        $this->assertEquals('pass', $rows[0]['fields'][1]['value']);
        $this->assertEquals('fail', $rows[1]['fields'][1]['value']);
        $this->assertEquals('fail', $rows[2]['fields'][1]['value']);
    }
}
