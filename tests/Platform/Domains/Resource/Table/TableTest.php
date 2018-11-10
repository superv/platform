<?php

namespace Tests\Platform\Domains\Resource\Table;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Table\Table;
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

    protected function setUp()
    {
        parent::setUp();

        Schema::create('t_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $this->groups = Resource::of('t_groups');
        $this->groups->create(['id' => 50, 'title' => 'Users']);
        $this->groups->create(['id' => 123, 'title' => 'Admins']);

        Schema::create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('age');
            $table->text('bio')->hide('table');

//            $table->text('bio')->visibility(function (Visibility $visibility) {
//                $visibility->hideIf()->scopeIs('table');
//            });

            $table->belongsTo('t_groups', 'group')->nullable();
        });

        $this->users = Resource::of('t_users');

        $this->config = new TableConfig();
        $this->config->setFieldsProvider($this->users);
        $this->config->setQueryProvider($this->users);
        $this->config->setActions([Action::make('edit'), Action::make('delete')]);
        $this->config->build();
    }

    /** @test */
    function builds_table_config()
    {
        $this->assertEquals(sv_url('sv/tables/'.$this->config->uuid()), $this->config->getUrl());
        $this->assertEquals(3, $this->config->getColumns()->count());

        $configArray = $this->config->compose();
        $this->assertEquals($this->config->getUrl(), array_get($configArray, 'config.dataUrl'));

        $columns = collect(array_get($configArray, 'config.meta.columns'))->keyBy('name');
        $this->assertEquals(['label' => 'Name', 'name' => 'name'], $columns->get('name'));
    }

    /** @test */
    function builds_table_rows()
    {
        $fakeA = $this->users->fake(['group_id' => 123]);

        [$fakeB, $fakeC, $fakeD] = $this->users->fake([], 3);
        $table = Table::config($this->config)->build();

        $this->assertTrue($this->config->isBuilt());
        $this->assertTrue($table->isBuilt());

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

        $this->withoutExceptionHandling();

        $configArray = $this->config->compose();

        $response = $this->getJsonUser($this->users->route('index'));
        $this->assertEquals(array_get($configArray, 'config.meta.columns'), $response->decodeResponseJson('data.props.page.blocks.0.props.block.props.config.meta.columns'));

        $response = $this->getJsonUser('sv/tables/'.$this->config->uuid());
        $this->assertEquals($table->compose(), $response->decodeResponseJson('data'));
    }
}