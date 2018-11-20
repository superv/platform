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
//        $this->makeTableConfig();
        $this->config = new TableConfig();
        $this->config->setFieldsProvider($this->users);
        $this->config->setQueryProvider($this->users);
        $this->config->setRowActions([EditEntryAction::class, DeleteEntryAction::class]);
        $this->config->setContextActions([CreateEntryAction::class]);
        $this->config->build();

        $this->assertTrue($this->config->isBuilt());
        $this->assertEquals(sv_url('sv/tables/'.$this->config->uuid().'/data'), $this->config->getDataUrl());
        $this->assertEquals(3, $this->config->getFields()->count());

        $composition = $this->config->compose();
        $this->assertEquals($this->config->getDataUrl(), $composition->get('config.dataUrl'));

        $columns = collect($composition->get('config.meta.columns'))->keyBy('name');
        $this->assertEquals(['label' => 'Name', 'name' => 'name'], $columns->get('name'));
    }

    function test__config_can_set_custom_data_url()
    {
        $this->config = new TableConfig;
        $this->config->setDataUrl('sv/custom/url');

        $this->assertEquals('sv/custom/url', $this->config->getDataUrl());
    }

    function test__builds_table_rows_with_resource_entry_models()
    {
        $this->makeGroupResource();
        $this->makeUserResource();
        $this->makeTableConfig();

        $fakeA = $this->users->fake(['group_id' => 123]);
        $this->users->fake([], 3);

        $table = $this->config->makeTable();

        $this->assertEquals(4, $table->getRows()->count());
        $this->assertEquals([
            'id'    => $fakeA->id,
            'name'  => $fakeA->name,
            'age'   => $fakeA->age,
            'group' => 'Admins',
        ], $table->getRows()->get(0)->getValues());

        $rowActions = $table->getRows()->first()->getActions();
        $this->assertEquals([
            ['name' => 'edit', 'title' => 'Edit', 'url' => $fakeA->route('edit')],
            ['name' => 'delete', 'title' => 'Delete', 'url' => $fakeA->route('delete')],
        ], $rowActions);

        $composition = $this->config->compose();

        $response = $this->getJsonUser($this->users->route('index'));
        $this->assertEquals(
            $composition->get('config.meta.columns'),
            $response->decodeResponseJson('data.props.blocks.0.props.block.props.config.meta.columns')
        );

        $response = $this->getJsonUser($this->config->getDataUrl());
        $this->assertEquals($table->compose(), $response->decodeResponseJson('data'));
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

        $this->makeTableConfig();
        $this->users->fake([], 3);

        $table = $this->config->makeTable();
        $rows = $table->getRows();
        $this->assertEquals(3, $rows->count());
    }

    protected function makeTableConfig(): void
    {
        $this->config = new TableConfig();
        $this->config->setFieldsProvider($this->users);
        $this->config->setQueryProvider($this->users);
        $this->config->setRowActions([EditEntryAction::class, DeleteEntryAction::class]);
        $this->config->build();
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
            $table->integer('age');
            $table->text('bio')->hide('table');
            $table->nullableBelongsTo('t_groups', 'group');
        });
    }
}

class TestUser extends Entry
{
    public $timestamps = false;

    protected $table = 't_users';
}