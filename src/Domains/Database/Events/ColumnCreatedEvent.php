<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Events\BaseEvent;

class ColumnCreatedEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition
     */
    public $column;

    /**
     * @var string
     */
    public $model;

    public function __construct(string $table, ColumnDefinition $column, $model)
    {
        $this->table = $table;
        $this->column = $column;
        $this->model = $model;
    }
}