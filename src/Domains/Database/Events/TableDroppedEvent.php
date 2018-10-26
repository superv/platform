<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Events\BaseEvent;

class TableDroppedEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    public function __construct($table)
    {
        $this->table = $table;
    }
}