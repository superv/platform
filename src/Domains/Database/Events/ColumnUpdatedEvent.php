<?php

namespace SuperV\Platform\Domains\Database\Events;

use Illuminate\Support\Fluent;
use SuperV\Platform\Events\BaseEvent;

class ColumnUpdatedEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var \Illuminate\Support\Fluent
     */
    public $column;

    public function __construct(string $table, Fluent $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
}