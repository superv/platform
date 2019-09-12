<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Resource\ResourceConfig;
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

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    public $config;

    public function __construct(ResourceConfig $config, $blueprint, ColumnDefinition $column)
    {
        $this->table = $config->getDriver()->getParam('table');
        $this->column = $column;
        $this->blueprint = $blueprint;
        $this->config = $config;
    }
}
