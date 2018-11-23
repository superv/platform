<?php

namespace Tests\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Action\DeleteEntryAction;
use SuperV\Platform\Domains\Resource\Action\EditEntryAction;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TableTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $users;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $groups;

    /** @var \SuperV\Platform\Domains\Resource\Table\TableConfig */
    protected $config;

    function test__builds_table_config()
    {
        $this->makeGroupResource();
        $this->makeUserResource();

        $config = TableConfig::make()
                                   ->setDataUrl('url/to/table/data')
                                   ->setColumns($this->users)
                                   ->setQuery($this->users)
                                   ->setRowActions([EditEntryAction::class, DeleteEntryAction::class])
                                   ->setContextActions([CreateEntryAction::class])
                                   ->build();

        $this->assertEquals(3, $config->getColumns()->count());

        $composition = $config->compose();
        $this->assertEquals($config->getDataUrl(), $composition->get('config.dataUrl'));

        $columns = collect($composition->get('config.meta.columns'))->keyBy('name');
        $this->assertEquals(['label' => 'Name', 'name' => 'name'], $columns->get('name'));
    }

    function test__builds_table_rows_with_resource_entry_models()
    {
        $this->makeGroupResource();
        $this->makeUserResource();
        $config = $this->makeTableConfig();

        $fakeA = $this->users->fake(['group_id' => 123]);
        $this->users->fake([], 3);

        $table = $config->makeTable();

        // row values
        $this->assertEquals(4, $table->getRows()->count());
        $this->assertEquals([
            'id'    => $fakeA->id,
            'name'  => $fakeA->name,
            'age'   => $fakeA->age,
            'group' => 'Admins',
        ], $table->getRows()->get(0)->getValues());

        // rows actions
        $rowActions = $table->getRows()->first()->getActions();
        $this->assertEquals(
            ['name' => 'edit', 'title' => 'Edit', 'url' => $fakeA->route('edit')],
            $rowActions[0]['props']
        );
        $this->assertEquals(
            ['name' => 'delete', 'title' => 'Delete', 'url' => $fakeA->route('delete')],
            $rowActions[1]['props']
        );

        // over http
        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($this->users->route('index'));
        $response->assertOk();


        $this->assertEquals(
            $config->compose()->get('config.meta.columns'),
            $response->decodeResponseJson('data.props.blocks.0.props.block.props.config.meta.columns')
        );

//        $configResponse = $response->decodeResponseJson('data.props.blocks.0.props.block.props.config');
//        $dataUrl = $configResponse['dataUrl'];
//        $response = $this->getJsonUser($dataUrl, ['data' => 1]);
//
//        $this->assertEquals($table->compose(), $response->decodeResponseJson('data.rows'));
    }

    function test__builds_table_rows_with_base_entry_models()
    {
        $this->users = $this->create('t_users',
            function (Blueprint $table, ResourceBlueprint $resource) {
                $resource->resourceKey('user');
                $resource->model(TestUser::class);
                $table->increments('id');
                $table->string('name');
            });

        $config = $this->makeTableConfig();
        TestUser::create(['name' => 'A']);
        TestUser::create(['name' => 'B']);

        $table = $config->makeTable();
        $rows = $table->getRows();
        $this->assertEquals(2, $rows->count());
    }

    protected function makeTableConfig(): TableConfig
    {
        return TableConfig::make()
                          ->setDataUrl('url/to/table/data')
                          ->setColumns($this->users)
                          ->setQuery($this->users)
                          ->setRowActions([EditEntryAction::class, DeleteEntryAction::class])
                          ->setContextActions([CreateEntryAction::class])
                          ->build();
    }

    protected function makeGroupResource(): void
    {
        $this->groups = $this->create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $this->groups->create(['id' => 50, 'title' => 'Users']);
        $this->groups->create(['id' => 123, 'title' => 'Admins']);
    }

    protected function makeUserResource(): void
    {
        $this->users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('age')->showOnIndex();
            $table->text('bio')->hide('table');
            $table->nullableBelongsTo('t_groups', 'group')->showOnIndex();
        });
    }
}

class TestUser extends Entry
{
    public $timestamps = false;

    protected $table = 't_users';
}