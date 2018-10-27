<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Events\BaseEvent;

class ColumnDroppedEvent extends BaseEvent
{
    public $table;

    public $columnName;

    public function __construct($table, $columnName)
    {
        $this->table = $table;
        $this->columnName = $columnName;
    }
}