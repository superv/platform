<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Events\BaseEvent;

class TableCreatingEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var array
     */
    public $columns;

    public function __construct($table, array $columns = [])
    {
        $this->table = $table;
        $this->columns = $columns;
    }
}