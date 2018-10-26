<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Events\BaseEvent;

class ColumnDroppedEvent extends BaseEvent
{
    public $table;

    public $column;

    public function __construct($table, $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
}