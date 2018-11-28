<?php

namespace Tests\Platform\Domains\Resource\Table;

use Illuminate\Database\Query\Builder;
use Mockery;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Table\TableColumn;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class TableColumnTest
{
    function test__construct()
    {
        $column = TableColumn::make('user_email');

        $this->assertEquals('user_email', $column->getName());
        $this->assertEquals('User Email', $column->getLabel());
    }

    function test__presenter()
    {
        $column = TableColumn::make('name');

        $column->setPresenter(function ($value) { return strtoupper($value); });

        $this->assertNotNull($presenter = $column->getPresenter());
        $this->assertEquals('ABC', $presenter('abc'));
    }

    function test__template()
    {
        $column = TableColumn::make('name');
        $column->setTemplate('{first} {last}');

        $entry = new ResourceEntry([
            'first' => 'Omar',
            'last'  => 'bin Hattab',
        ]);
        $this->assertEquals('Omar bin Hattab', $column->getPresenter()($entry));
    }

    function test__create_from_field()
    {
        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->belongsTo('t_groups', 'group');
        });

        $email = $users->getField('email')->setLabel('User Email');
        $emailColumn = TableColumn::fromField($email);
        $this->assertEquals('email', $emailColumn->getName());
        $this->assertEquals('User Email', $emailColumn->getLabel());

        $group = $users->getField('group');
        $groupColumn = TableColumn::fromField($group);

        $this->assertNotNull($groupColumn->getAlterQueryCallback());
    }

    function test__value_from_relation()
    {
        $userMock = Mockery::mock(EntryContract::class);
        $userMock->group = Mockery::mock(EntryContract::class);
        $userMock->group->name = 'Users';

        $col = TableColumn::make('group.name');
        $this->assertEquals('Users', $col->getPresenter()($userMock));

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('with')->with('group')->once();

        $this->assertNotNull($callback = $col->getAlterQueryCallback());
        $callback($query);
    }
}
