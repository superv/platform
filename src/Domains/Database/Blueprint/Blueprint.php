<?php

namespace SuperV\Platform\Domains\Database\Blueprint;

use Current;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint as LaravelBlueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use SuperV\Platform\Domains\Database\ColumnDefinition;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppedEvent;
use SuperV\Platform\Domains\Database\Schema;

class Blueprint extends LaravelBlueprint
{
    use CreatesRelations;
    use CreatesFields;

    /**
     * @var \SuperV\Platform\Domains\Database\Schema
     */
    protected $builder;

    public function __construct(string $table, ?\Closure $callback = null, Schema $builder = null)
    {
        parent::__construct($table, $callback);

        $this->builder = $builder;
    }

    public function addColumn($type, $name, array $parameters = [])
    {
        // Here, while adding a column let's pass along
        // resource blueprint to each column
        $this->columns[] = $column = new ColumnDefinition(
            $this->builder ? $this->builder->resource() : new \SuperV\Platform\Domains\Resource\ResourceBlueprint,
            array_merge(compact('type', 'name'), $parameters)
        );

        return $column;
    }

    public function build(Connection $connection, Grammar $grammar)
    {
        if ($this->dropping()) {
            if (! $this->builder->justRun) {
                parent::build($connection, $grammar);
            }

            TableDroppedEvent::dispatch($this->tableName());

            return;
        }

        if ($this->creating()) {
            TableCreatingEvent::dispatch($this->tableName(), $this->columns, $this->builder->resource(), Current::migrationScope());
        } else {
            // Dropping Columns
            $this->runDropOperations();
        }

        sv_collect($this->getChangedColumns())->map(function (Fluent $column) {
            ColumnUpdatedEvent::dispatch($this->tableName(), $column);
        });

        sv_collect($this->getAddedColumns())->map(function ($column) {
            ColumnCreatedEvent::dispatch($this->tableName(), $column, $this->builder->resource()->model);
        });

        $this->columns = array_filter($this->columns, function ($column) {
            return ! $column->ignore;
        });

        if (! $this->builder->justRun) {
            parent::build($connection, $grammar);
        }

        if ($this->creating()) {
            TableCreatedEvent::dispatch($this->tableName(), $this->columns);
        }
    }

    public function tableName()
    {
        return $this->table;
    }

    public function dropping()
    {
        return collect($this->commands)->contains(function ($command) {
            return $command->name == 'drop' || $command->name == 'dropIfExists';
        });
    }

    public function getColumnNames(): array
    {
        return sv_collect($this->getColumns())->pluck('name')->all();
    }

    public function runDropOperations(): void
    {
        foreach ($this->commands as $command) {
            if ($command->name === 'dropColumn') {
                sv_collect($command->columns)->map(function ($column) {
                    ColumnDroppedEvent::dispatch($this->tableName(), $column);
                });
            }
        }
    }
}