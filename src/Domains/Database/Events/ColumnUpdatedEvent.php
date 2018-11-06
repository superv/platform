<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Events\BaseEvent;

class ColumnUpdatedEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var \SuperV\Platform\Domains\Database\Schema\ColumnDefinition
     */
    public $column;

    public function __construct(string $table, ColumnDefinition $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
}