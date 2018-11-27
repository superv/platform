<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceIndexTest extends ResourceTestCase
{
    function test__bsmllh()
    {
        $users = $this->schema()->users();

        $page = $this->getPageFromUrl($users->route('index'));
        $table = HelperComponent::from($page->getProp('blocks.0'));

        $this->assertEquals('sv-loader', $table->getName());
        $this->assertEquals(sv_url($users->route('index.table')), $table->getProp('url'));

    }

    function test__index_table_config()
    {
        $users = $this->schema()->users();

        $response = $this->getJsonUser($users->route('index.table'))->assertOk();
        $table = HelperComponent::from($response->decodeResponseJson('data'));

        $this->assertEquals(sv_url($users->route('index.table').'/data'), $table->getProp('config.data_url'));

        $fields = $table->getProp('config.fields');
        $this->assertEquals(3, count($fields));
        foreach ($fields as $key => $field) {
            $this->assertTrue(is_numeric($key));
            $this->assertEquals([
                'uuid',
                'name',
                'label',
            ], array_keys($field));

            $rowActions = $table->getProp('config.row_actions');
            $this->assertEquals(1, count($rowActions));

            $this->assertEquals([
                'name' => 'view',
                'title' => 'View',
                'url' => 'sv/res/t_users/{entry.id}/view'
            ], $rowActions[0]['props']);
        }
    }

    function test__index_table_data()
    {
        $this->withoutExceptionHandling();
        $users = $this->schema()->users();
        $userA = $users->fake(['group_id' => 1]);
        $userB = $users->fake(['group_id' => 2]);

        $response = $this->getJsonUser($users->route('index.table').'/data')->assertOk();

        $rows = $response->decodeResponseJson('data.rows');
        $this->assertEquals(2, count($rows));

        $rowA = $rows[0];
        $this->assertEquals($userA->getId(), $rowA['id']);

        $label = $rowA['fields'][0];
        $this->assertEquals(['type', 'name', 'value',], array_keys($label));

        $this->assertEquals('text', $label['type']);
        $this->assertEquals('label', $label['name']);
        $this->assertEquals($users->getEntryLabel($userA), $label['value']);

        $age = $rowA['fields'][1];
        $this->assertEquals('number', $age['type']);
        $this->assertEquals('age', $age['name']);
        $this->assertSame((int)$userA->age, $age['value']);

        $group = $rowA['fields'][2];
        $this->assertEquals('belongs_to', $group['type']);
        $this->assertEquals('group_id', $group['name']);

        $groups = sv_resource('t_groups');
        $usersGroup = $groups->find(1);
        $this->assertSame($usersGroup->title, $group['value']);
        $this->assertEquals($groups->route('view', $usersGroup), $group['meta']['link']);
    }
}

