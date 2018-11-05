<?php

namespace SuperV\Platform\Domains\Database\Events;

use SuperV\Platform\Domains\Resource\ResourceBlueprint;
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
    public $scope;

    /** @var \SuperV\Platform\Domains\Resource\ResourceBlueprint  */
    public $blueprint;

    public function __construct($table, array $columns = [], ResourceBlueprint $blueprint, $scope)
    {
        $this->table = $table;
        $this->columns = $columns;
        $this->blueprint = $blueprint;
        $this->scope = $scope;
    }
}