<?php

namespace SuperV\Platform\Domains\Database\Events;

use Illuminate\Support\Fluent;
use SuperV\Platform\Events\BaseEvent;

class ColumnCreatedEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var \Illuminate\Database\Schema\ColumnDefinition
     */
    public $column;

    /**
     * @var string
     */
    public $model;

    public function __construct(string $table, Fluent $column, $model)
    {
        $this->table = $table;
        $this->column = $column;
        $this->model = $model;
    }
}