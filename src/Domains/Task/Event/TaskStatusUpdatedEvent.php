<?php

namespace SuperV\Platform\Domains\Task\Event;

use Illuminate\Broadcasting\Channel;
use SuperV\Platform\Domains\Task\Model\TaskModel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TaskStatusUpdatedEvent implements ShouldBroadcast
{
    /**
     * @var TaskModel
     */
    public $model;

    /**
     * @var
     */
    public $label;

    public function __construct(TaskModel $model, $label)
    {
        $this->model = $model;
        $this->label = $label;
    }

    public function broadcastOn()
    {
        return new Channel('Tasks');
    }

    public function broadcastAs()
    {
        return $this->model->parent_id ? 'subtask.status' : 'task.status';
    }
}
