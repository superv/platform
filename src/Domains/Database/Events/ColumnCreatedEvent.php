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

    /**
     * @var \SuperV\Platform\Domains\Database\Schema\Blueprint
     */
    public $blueprint;

    public function __construct(string $table, $blueprint, ColumnDefinition $column, $model = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->model = $model;
        $this->blueprint = $blueprint;
    }
}