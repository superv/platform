<?php namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Platform\Domains\Model\EloquentModel;

class TaskModel extends EloquentModel
{
    protected $table = 'platform_tasks';

    protected $casts = [
        'payload' => 'json'
    ];
}