<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Events\BaseEvent;

class ColumnDroppedEvent extends BaseEvent
{
    public $columnName;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    public $config;

    public function __construct(ResourceConfig $config, $columnName)
    {
        $this->columnName = $columnName;
        $this->config = $config;
    }
}
