<?php

namespace SuperV\Platform\Domains\Database;

use Closure;
use Current;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppedEvent;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * @var \SuperV\Platform\Domains\Database\Schema
     */
    protected $builder;

    public function __construct(string $table, ?\Closure $callback = null, Schema $builder = null)
    {
        parent::__construct($table, $callback);

        $this->builder = $builder;
    }

    public function model($model)
    {
        $this->builder->setModel($model);
    }

    public function select($name): ColumnDefinition
    {
        return $this->string($name)->fieldType('select');
    }

    public function email($name)
    {
        return $this->string($name)->fieldType('email');
    }

    public function file($name)
    {
        return $this->addColumn(null, $name)->fieldType('file')->ignore();
    }

    public function hasMany($related, $relation, $foreignKey = null, $localKey = null)
    {
        return $this->addColumn(null, $relation)
                    ->nullable()
                    ->relation([
                        'type'        => 'has_many',
                        'related'     => $related,
                        'foreign_key' => $foreignKey,
                        'local_key'   => $localKey,
                    ]);
    }

    /**
     * Add a new column to the blueprint.
     *
     * @param  string $type
     * @param  string $name
     * @param  array  $parameters
     * @return \SuperV\Platform\Domains\Database\ColumnDefinition
     */
    public function addColumn($type, $name, array $parameters = [])
    {
        $this->columns[] = $column = new ColumnDefinition(
            array_merge(compact('type', 'name'), $parameters)
        );

        return $column;
    }

    public function nullableBelongsTo($related, $relation, $foreignKey = null, $ownerKey = null)
    {
        return $this->belongsTo($related, $relation, $foreignKey, $ownerKey)->nullable();
    }

    public function belongsTo($related, $relation, $foreignKey = null, $ownerKey = null)
    {
        return $this->addColumn(null, $relation.'_id')//str_replace_last('_id', '', $relation)
                    ->relation([
            'type'        => 'belongs_to',
            'related'     => $related,
            'foreign_key' => $foreignKey,
            'owner_key'   => $ownerKey,
        ]);
    }

    public function belongsToMany(
        $related,
        $relation,
        $pivotTable = null,
        $pivotForeignKey = null,
        $pivotRelatedKey = null,
        Closure $pivotColumns = null
    ) {
        return $this->addColumn(null, $relation)
                    ->nullable()
                    ->relation([
                        'type'              => 'belongs_to_many',
                        'related'           => $related,
                        'pivot_table'       => $pivotTable,
                        'pivot_foreign_key' => $pivotForeignKey,
                        'pivot_related_key' => $pivotRelatedKey,
                        'pivot_columns'     => $pivotColumns,
                    ]);
    }

    public function morphToMany(
        $related,
        $relation,
        $morphName,
        $pivotTable = null,
        $pivotRelatedKey = null,
        Closure $pivotColumns = null
    ) {
        return $this->addColumn(null, $relation)
                    ->nullable()
                    ->relation([
                        'type'              => 'morph_to_many',
                        'related'           => $related,
                        'pivot_table'       => $pivotTable,
                        'pivot_foreign_key' => $morphName.'_id',
                        'pivot_related_key' => $pivotRelatedKey,
                        'pivot_columns'     => $pivotColumns,
                        'morph_name'        => $morphName,
                    ]);
    }

    public function build(Connection $connection, Grammar $grammar)
    {
        if ($this->dropping()) {
            if (! $this->builder->doNotDo) {
                parent::build($connection, $grammar);
            }

            TableDroppedEvent::dispatch($this->tableName());

            return;
        }

        if ($this->creating()) {
            TableCreatingEvent::dispatch($this->tableName(), $this->columns, $this->builder->getModel(), Current::migrationScope());
        } else {
            // Dropping Columns
            foreach ($this->commands as $command) {
                if ($command->name === 'dropColumn') {
                    sv_collect($command->columns)->map(function ($column) {
                        ColumnDroppedEvent::dispatch($this->tableName(), $column);
                    });
                }
            }
        }

        sv_collect($this->getChangedColumns())->map(function (Fluent $column) {
            ColumnUpdatedEvent::dispatch($this->tableName(), $column);
        });

        sv_collect($this->getAddedColumns())->map(function ($column) {
            ColumnCreatedEvent::dispatch($this->tableName(), $column, $this->builder->getModel());
        });

        $this->columns = array_filter($this->columns, function ($column) {
            return ! $column->ignore;
        });

        if (! $this->builder->doNotDo) {
            parent::build($connection, $grammar);
        }

        if ($this->creating()) {
            TableCreatedEvent::dispatch($this->tableName(), $this->columns);
        }
    }

    /**
     * Determine if the blueprint has a drop or dropIfExists command.
     *
     * @return bool
     */
    protected function dropping()
    {
        return collect($this->commands)->contains(function ($command) {
            return $command->name == 'drop' || $command->name == 'dropIfExists';
        });
    }

    protected function tableName()
    {
        return $this->table;
    }

    public function getColumnNames(): array
    {
        return sv_collect($this->getColumns())->pluck('name')->all();
    }
}