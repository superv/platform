<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceIndexTest extends ResourceTestCase
{
    function test__bsmllh()
    {
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

    function test__index_table_config()
    {
        $users = $this->schema()->users();

        $response = $this->getJsonUser($users->route('index.table'));
        $response->assertOk();
        $table =  HelperComponent::from($response->decodeResponseJson('data'));

        $this->assertEquals(sv_url($users->route('index.table') .'/data'), $table->getProp('config.data_url'));

        $fields = $table->getProp('config.fields');
        $this->assertEquals(3, count($fields));
        foreach($fields as $key => $field) {
            $this->assertTrue(is_numeric($key));

            $this->assertEquals([
                   'uuid', 'name',  'label'
               ], array_keys($field));

        }
    }

    function test__index_table_data()
    {
        $this->withoutExceptionHandling();
        $users = $this->schema()->users();
        $userA = $users->fake(['group_id' => 1]);
        $userB = $users->fake(['group_id' => 2]);

        $response = $this->getJsonUser($users->route('index.table'). '/data');
        $response->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(2, count($rows));

        $this->assertEquals(3, count($rows[0]['fields']));

        foreach($rows[0]['fields'] as $key => $field) {
            $this->assertTrue(is_numeric($key));
            $this->assertEquals([
                'type', 'name',  'value'
            ], array_keys($field));
        }
        $this->assertEquals('text', $rows[0]['fields'][0]['type']);
        $this->assertEquals('label', $rows[0]['fields'][0]['name']);
        $this->assertEquals($users->getEntryLabel($userA), $rows[0]['fields'][0]['value']);

        $this->assertEquals('number', $rows[0]['fields'][1]['type']);
        $this->assertEquals('age', $rows[0]['fields'][1]['name']);
        $this->assertSame((int)$userA->age, $rows[0]['fields'][1]['value']);

        $this->assertEquals('belongs_to', $rows[0]['fields'][2]['type']);
        $this->assertEquals('group_id', $rows[0]['fields'][2]['name']);
        $this->assertSame('Users', $rows[0]['fields'][2]['value']);
    }
}

