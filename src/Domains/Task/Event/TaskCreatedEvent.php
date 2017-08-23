<?php namespace SuperV\Platform\Domains\Task\Event;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use SuperV\Platform\Domains\Task\Model\TaskModel;

class TaskCreatedEvent implements ShouldBroadcast
{
    /**
     * @var TaskModel
     */
    public $model;

    public function __construct(TaskModel $model)
    {
        $this->model = $model;
    }

    public function broadcastOn()
    {
        return new Channel('Tasks');
    }

    public function broadcastAs()
    {
        return 'task.created';
    }
}