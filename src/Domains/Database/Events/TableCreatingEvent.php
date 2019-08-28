<?php

namespace SuperV\Platform\Domains\Database\Events;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Events\BaseEvent;

class TableCreatingEvent extends BaseEvent
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
     * @var string
     */
    public $namespace;

    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    public $config;

    /**
     * @var \SuperV\Platform\Domains\Database\Schema\Blueprint
     */
    public $blueprint;

    public function __construct($table, Collection $columns, ResourceConfig $config, $namespace, Blueprint $blueprint)
    {
        $this->table = $table;
        $this->columns = $columns;
        $this->config = $config;
        $this->namespace = $namespace;
        $this->blueprint = $blueprint;
    }
}
