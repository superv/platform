<?php

namespace SuperV\Platform\Domains\Database\Events;

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

    public $model;

    public function __construct($table, array $columns = [], $model, $scope)
    {
        $this->table = $table;
        $this->columns = $columns;
        $this->scope = $scope;
        $this->model = $model;
    }
}