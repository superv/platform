<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Events\BaseEvent;

class TableDroppedEvent extends BaseEvent
{
    /**
     * Table name
     *
     * @var string
     */
    public $table;

    /**
     * Database connection name
     *
     * @var string
     */
    public $connection;

    public function __construct($table, string $connection)
    {
        $this->table = $table;
        $this->connection = $connection;
    }
}