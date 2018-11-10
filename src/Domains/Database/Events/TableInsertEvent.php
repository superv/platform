<?php

namespace SuperV\Platform\Domains\Database\Events;


use Illuminate\Foundation\Events\Dispatchable;

class TableInsertEvent
{
    use Dispatchable;

    /**
     * @var string
     */
    public $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }
}