<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class RelationIndexTest extends ResourceTestCase
{
    function test__index_listing_with_morph_to_many_relations()
    {
        $this->schema()->groups();
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