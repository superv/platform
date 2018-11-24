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


        $this->assertEquals(['label' => 'T User', 'name' => 'label'], $columns->get('label'));
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