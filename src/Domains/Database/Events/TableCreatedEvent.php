<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Events\BaseEvent;

class TableCreatedEvent extends BaseEvent
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var array
     */
    public $columns;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    public $config;

    public function __construct($table, ResourceConfig $config, array $columns = [])
    {
        $this->table = $table;
        $this->columns = $columns;
        $this->config = $config;
    }
}
