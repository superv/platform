<?php

namespace Tests\Platform\Domains\Database;

use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppedEvent;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
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
                $this->assertEquals('superv.platform', $event->addon);
                $this->assertEquals('tasks', $event->table);
                $this->assertArrayContains(['id', 'title'], sv_collect($event->columns)->pluck('name')->all());
                $this->assertFalse(\Schema::hasTable('tasks'));

                $dispatchedEvents->tableCreating = true;
            });

        $this->app['events']->listen(TableCreatedEvent::class, function (TableCreatedEvent $event) use (
            $dispatchedEvents
        ) {
            $this->assertEquals('tasks', $event->table);
            $this->assertArrayContains(['id', 'title'], sv_collect($event->columns)->pluck('name')->all());
            $this->assertTrue(\Schema::hasTable('tasks'));

            $dispatchedEvents->tableCreated = true;
        });

        $migrator = $this->app['migrator'];
        $migrator->setAddon('superv.platform');
        $migrator->run(__DIR__.'/__fixtures__/migrations');

        $this->assertTrue($dispatchedEvents->tableCreating);
        $this->assertTrue($dispatchedEvents->tableCreated);
    }

    function test__dispatch_event_when_a_table_is_dropped()
    {
        Event::fake(TableDroppedEvent::class);

        \SuperV\Platform\Domains\Database\Schema\Schema::create('tasks', function (Blueprint $table) {
            $table->string('title');
        });

        \SuperV\Platform\Domains\Database\Schema\Schema::drop('tasks');

        Event::assertDispatched(TableDroppedEvent::class, function (TableDroppedEvent $event) {
            return $event->table === 'tasks';
        });
    }

    function test__dispatch_event_when_a_column_is_created()
    {
        Event::fake(ColumnCreatedEvent::class);

        $columns = sv_collect();
        $dispatchedColumns = sv_collect();

        \SuperV\Platform\Domains\Database\Schema\Schema::create('tasks', function (Blueprint $table) use ($columns) {
            $columns->put('title', $table->string('title'));
            $columns->put('priority', $table->string('priority'));
        });

        \SuperV\Platform\Domains\Database\Schema\Schema::table('tasks', function (Blueprint $table) use ($columns) {
            $columns->put('description', $table->string('description')->nullable());
        });

        Event::assertDispatched(
            ColumnCreatedEvent::class,
            function (ColumnCreatedEvent $event) use ($columns, $dispatchedColumns) {
                $this->assertEquals('tasks', $event->table);
                $this->assertTrue($columns->has($event->column->name));

                $dispatchedColumns->push($event->column);

                return true;
            });

        $this->assertEquals($columns->count(), $dispatchedColumns->count());
    }

    function test__dispatch_event_when_a_column_is_updated()
    {
        Event::fake(ColumnUpdatedEvent::class);

        \SuperV\Platform\Domains\Database\Schema\Schema::create('tasks', function (Blueprint $table) {
            $table->string('title', 50);
        });

        $columns = sv_collect();
        \SuperV\Platform\Domains\Database\Schema\Schema::table('tasks', function (Blueprint $table) use ($columns) {
            $columns->put('title', $table->string('title', 100)->change());
        });

        Event::assertDispatched(ColumnUpdatedEvent::class, function (ColumnUpdatedEvent $event) use ($columns) {
            $this->assertEquals('tasks', $event->table);

            return $columns->has($event->column->name);
        });
    }

    function test__dispatch_event_when_a_column_is_dropped()
    {
        Event::fake(ColumnDroppedEvent::class);
        \SuperV\Platform\Domains\Database\Schema\Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority');
        });

        \SuperV\Platform\Domains\Database\Schema\Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['title', 'priority']);
        });

        Event::assertDispatched(ColumnDroppedEvent::class, function (ColumnDroppedEvent $event) {
            return $event->table === 'tasks' && $event->columnName === 'title';
        });
        Event::assertDispatched(ColumnDroppedEvent::class, function (ColumnDroppedEvent $event) {
            return $event->table === 'tasks' && $event->columnName === 'priority';
        });
    }
}