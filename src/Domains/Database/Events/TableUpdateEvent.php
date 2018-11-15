<?php

namespace SuperV\Platform\Domains\Database\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TableUpdateEvent
{
    use Dispatchable;

    /**
     * @var string
     */
    public $table;

    public $rowId;

    public function __construct(string $table, $rowId)
    {
        $this->table = $table;
        $this->rowId = $rowId;
    }
}