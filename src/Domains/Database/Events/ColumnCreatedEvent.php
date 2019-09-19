<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Database\Schema\ColumnDefinition;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Events\BaseEvent;

class ColumnCreatedEvent extends BaseEvent
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
     * @var string
     */
    public $model;

    /**
     * @var \SuperV\Platform\Domains\Database\Schema\Blueprint
     */
    public $blueprint;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceConfig
     */
    public $config;

    public function __construct(ResourceConfig $config, $blueprint, ColumnDefinition $column, $model = null)
    {
        $this->table = $config->getDriver()->getParam('table');
        $this->column = $column;
        $this->model = $model;
        $this->blueprint = $blueprint;
        $this->config = $config;
    }
}
