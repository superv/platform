<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
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

    /**
     * @var \SuperV\Platform\Domains\Database\Schema\Blueprint
     */
    public $blueprint;

    public function __construct(string $table, Blueprint $blueprint, ColumnDefinition $column)
    {
        $this->table = $table;
        $this->column = $column;
        $this->blueprint = $blueprint;
    }
}