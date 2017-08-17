<?php namespace SuperV\Platform\Domains\Task\Features;

use Illuminate\Contracts\Queue\ShouldQueue;
use SuperV\Modules\Supreme\Domains\Server\Server;
use SuperV\Modules\Supreme\Domains\Service\Model\ServiceModel;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Task\Task;
use SuperV\Platform\Domains\Task\TaskListener;
use SuperV\Platform\Domains\Task\TaskResult;

class DeployTask extends Feature implements ShouldQueue
{
    /**
     * @var Task
     */
    private $task;

    /**
     * @var ServiceModel
     */
    private $service;

    public function __construct(Task $task, ServiceModel $service)
    {
        $this->task = $task;
        $this->service = $service;
    }

    public function handle(TaskResult $result)
    {
        $task = $this->task;
        $task->started();

        $remote = superv(Server::class)->onServer($task->server());

        try {
            foreach ($task->getCommands() as $command) {
                $this->dispatchNow((new $command($remote))->setListener(new TaskListener($task)));
            }

            $result->setSuccess(true);
        } catch (\Exception $e) {
            $result->setSuccess(false)
                   ->setMessage($e->getMessage());
        }

        $task->setResult($result);
    }
}