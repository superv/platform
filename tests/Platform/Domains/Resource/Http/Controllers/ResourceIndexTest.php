<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceIndexTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $this->schema()->groups();
        $users = $this->schema()->users();

        $userA = $users->fake(['group_id' => 1]);
        $userB = $users->fake(['group_id' => 2]);

        $page = $this->getPageFromUrl($users->route('index'));

        $table = HelperComponent::from($page->getProp('blocks.0'));

        $response = $this->getJsonUser($table->getProp('config.dataUrl'));
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(2, count($rows));
        $this->assertEquals(
            [
                'id'    => $userA->getId(),
                'label' => $userA->name,
                'age'   => $userA->age,
                'group' => 'Users',
            ], $rows[0]['values']
        );

        $this->assertEquals(
            [
                'id'    => $userB->getId(),
                'label' => $userB->name,
                'age'   => $userB->age,
                'group' => 'Clients',
            ], $rows[1]['values']
        );
    }
}

