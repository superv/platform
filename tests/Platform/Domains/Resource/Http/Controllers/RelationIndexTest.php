<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelationIndexTest extends ResourceTestCase
{
    function test__index_listing_with_has_many_relations()
    {
        $users = $this->schema()->users();
        $posts = $this->schema()->posts();

        $userA = $users->fake();
       $userPosts = $userA->posts()->createMany($posts->fakeMake([], 5));

        $userB = $users->fake();
        $userB->posts()->createMany($posts->fakeMake([], 3));
        $this->assertEquals(8, $posts->count());

        $url = route('relation.index', ['resource' => 't_users', 'id' => $userA->getId(), 'relation' => 'posts']);

        $response = $this->getJsonUser($url);
        $response->assertOk();

        $table = HelperComponent::from($response->decodeResponseJson('data'));
        $this->assertEquals(1, count($table->getProp('config.context.actions')));

        $action = HelperComponent::from($table->getProp('config.context.actions.0'));

        $this->assertEquals(
            route('relation.create', ['resource' => 't_users', 'id' => $userA->getId(), 'relation' => 'posts']),
            sv_url($action->getProp('url')));

        $response = $this->getJsonUser($table->getProp('config.dataUrl'));
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(5, count($rows));

        $viewAction = HelperComponent::from($rows[0]['actions'][0]);


        $this->assertEquals($userPosts->first()->route('view'), $viewAction->getProp('url'));
    }

    function test__index_listing_with_morph_to_many_relations()
    {
        $this->withoutExceptionHandling();

        $users = $this->schema()->users();
        $this->schema()->actions();

        $userA = $users->fake();

        $userA->actions()->attach([1 => ['provision' => 'pass']]);
        $userA->actions()->attach([2 => ['provision' => 'fail']]);
        $userA->actions()->attach([3 => ['provision' => 'fail']]);

        $url = route('relation.index', ['resource' => 't_users', 'id' => $userA->getId(), 'relation' => 'actions']);

        $response = $this->getJsonUser($url);
        $response->assertOk();

        $table = HelperComponent::from($response->decodeResponseJson('data'));

        $response = $this->getJsonUser($table->getProp('config.dataUrl'));
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(3, count($rows));

        $this->assertEquals('pass', $rows[0]['values']['provision']);
        $this->assertEquals('fail', $rows[1]['values']['provision']);
        $this->assertEquals('fail', $rows[2]['values']['provision']);
    }
}