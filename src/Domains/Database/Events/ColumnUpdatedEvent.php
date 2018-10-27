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
     * @var \Illuminate\Database\Schema\ColumnDefinition
     */
    public $column;

    public function __construct(string $table, Fluent $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
}