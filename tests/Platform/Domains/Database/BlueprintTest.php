<?php

namespace Tests\Platform\Domains\Database;

use Event;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppedEvent;
use SuperV\Platform\Domains\Database\Schema;
use Tests\Platform\TestCase;

class BlueprintTest extends TestCase
{
    /** @test */
    function dispatch_event_when_a_table_is_created()
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
                $this->assertEquals('superv.platform', $event->scope);
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

        $this->app['migrator']->run(__DIR__.'/__fixtures__/migrations');

        $this->assertTrue($dispatchedEvents->tableCreating);
        $this->assertTrue($dispatchedEvents->tableCreated);
    }

    /** @test */
    function dispatch_event_when_a_table_is_dropped()
    {
        Event::fake(TableDroppedEvent::class);

        Schema::create('tasks', function (Blueprint $table) {
            $table->string('title');
        });

        Schema::drop('tasks');

        Event::assertDispatched(TableDroppedEvent::class, function (TableDroppedEvent $event) {
            return $event->table === 'tasks';
        });
    }

    /** @test */
    function dispatch_event_when_a_column_is_created()
    {
        Event::fake(ColumnCreatedEvent::class);

        $columns = sv_collect();
        $dispatchedColumns = sv_collect();

        Schema::create('tasks', function (Blueprint $table) use ($columns) {
            $columns->put('title', $table->string('title'));
            $columns->put('priority', $table->string('priority'));
        });

        Schema::table('tasks', function (Blueprint $table) use ($columns) {
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

    /** @test */
    function dispatch_event_when_a_column_is_updated()
    {
        Event::fake(ColumnUpdatedEvent::class);

        Schema::create('tasks', function (Blueprint $table) {
            $table->string('title', 50);
        });

        $columns = sv_collect();
        Schema::table('tasks', function (Blueprint $table) use ($columns) {
            $columns->put('title', $table->string('title', 100)->change());
        });

        Event::assertDispatched(ColumnUpdatedEvent::class, function (ColumnUpdatedEvent $event) use ($columns) {
            $this->assertEquals('tasks', $event->table);

            return $columns->has($event->column->name);
        });
    }

    /** @test */
    function dispatch_event_when_a_column_is_dropped()
    {
        Event::fake(ColumnDroppedEvent::class);
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('priority');
        });

        Schema::table('tasks', function (Blueprint $table) {
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