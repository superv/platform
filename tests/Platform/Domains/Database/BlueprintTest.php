<?php

namespace Tests\Platform\Domains\Database;

use DB;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppedEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppingEvent;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use Tests\Platform\TestCase;

class BlueprintTest extends TestCase
{
    use RefreshDatabase;

    function test__dispatch_event_when_a_table_is_created()
    {
        $this->app['migrator']->run(__DIR__.'/migrations');

        $dispatchedEvents = new class
        {
            public $tableCreating = false;

            public $tableCreated = false;
        };

        $this->app['events']->listen(
            TableCreatingEvent::class,
            function (TableCreatingEvent $event) use ($dispatchedEvents) {
                $this->assertEquals('platform', $event->namespace);
                $this->assertEquals('testing_tasks', $event->table);
                $this->assertArrayContains(['id', 'title'], sv_collect($event->columns)->pluck('name')->all());
                $this->assertFalse(\Schema::hasTable('testing_tasks'));

                $dispatchedEvents->tableCreating = true;
            });

        $this->app['events']->listen(TableCreatedEvent::class, function (TableCreatedEvent $event) use (
            $dispatchedEvents
        ) {
            $this->assertEquals('testing_tasks', $event->table);
            $this->assertArrayContains(['id', 'title'], sv_collect($event->columns)->pluck('name')->all());
            $this->assertTrue(\Schema::hasTable('testing_tasks'));

            $dispatchedEvents->tableCreated = true;
        });

        /** @var \SuperV\Platform\Domains\Database\Migrations\Migrator $migrator */
        $migrator = $this->app['migrator'];
        $migrator->setNamespace('platform');
        $migrator->run(__DIR__.'/__fixtures__/migrations');

        $this->assertTrue($dispatchedEvents->tableCreating);
        $this->assertTrue($dispatchedEvents->tableCreated);
    }

    function test__dispatch_event_when_a_table_is_dropped()
    {
        Event::fake(TableDroppedEvent::class);

        Schema::create('testing_tasks', function (Blueprint $table) {
            $table->string('title');
        });

        Schema::drop('testing_tasks');

        Event::assertDispatched(TableDroppedEvent::class, function (TableDroppedEvent $event) {
            $this->assertTableDoesNotExist('testing_tasks');
            return $event->table === 'testing_tasks' && $event->connection === DB::getDefaultConnection();
        });
    }

    function test__dispatch_event_before_table_is_dropping()
    {
        $_SERVER['__table_dropping_event'] = null;

        Event::listen(TableDroppingEvent::class, function (TableDroppingEvent $event) {
            $this->assertTableExists('testing_tasks');
            $this->assertEquals('testing_tasks', $event->table);
            $this->assertEquals(DB::getDefaultConnection(), $event->connection);

            $_SERVER['__table_dropping_event'] = $event->table;
        });

        Schema::create('testing_tasks', function (Blueprint $table) { $table->string('title'); });
        Schema::drop('testing_tasks');

        $this->assertNotNull($_SERVER['__table_dropping_event']);
    }

    function test__dispatch_event_when_a_column_is_created()
    {
        Event::fake(ColumnCreatedEvent::class);

        $columns = sv_collect();
        $dispatchedColumns = sv_collect();

        Schema::create('testing_tasks', function (Blueprint $table) use (
            $columns
        ) {
            $columns->put('title', $table->string('title'));
            $columns->put('priority', $table->string('priority'));
        });

        Schema::table('testing_tasks', function (Blueprint $table) use (
            $columns
        ) {
            $columns->put('description', $table->string('description')->nullable());
        });

        Event::assertDispatched(
            ColumnCreatedEvent::class,
            function (ColumnCreatedEvent $event) use ($columns, $dispatchedColumns) {
                $this->assertEquals('testing_tasks', $event->table);
                $this->assertTrue($columns->has($event->column->name));

                $dispatchedColumns->push($event->column);

                return true;
            });

        $this->assertEquals($columns->count(), $dispatchedColumns->count());
    }

    function test__dispatch_event_when_a_column_is_updated()
    {
        Event::fake(ColumnUpdatedEvent::class);

        Schema::create('testing_tasks', function (Blueprint $table) {
            $table->string('title', 50);
        });

        $columns = sv_collect();
        Schema::table('testing_tasks', function (Blueprint $table) use ($columns) {
            $columns->put('title', $table->string('title', 100)->change());
        });

        Event::assertDispatched(ColumnUpdatedEvent::class,
            function (ColumnUpdatedEvent $event) use ($columns) {
                $this->assertEquals('testing_tasks', $event->table);

                return $columns->has($event->column->name);
            }
        );
    }

    function test__dispatch_event_when_a_column_is_dropped()
    {
        Event::fake(ColumnDroppedEvent::class);
        Schema::create('testing_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority');
        });

        Schema::table('testing_tasks', function (Blueprint $table) {
            $table->dropColumn(['title', 'priority']);
        });

        Event::assertDispatched(ColumnDroppedEvent::class, function (ColumnDroppedEvent $event) {
            return $event->config->getTable() === 'testing_tasks' && $event->columnName === 'title';
        });
        Event::assertDispatched(ColumnDroppedEvent::class, function (ColumnDroppedEvent $event) {
            return $event->config->getTable() === 'testing_tasks' && $event->columnName === 'priority';
        });
    }
}
