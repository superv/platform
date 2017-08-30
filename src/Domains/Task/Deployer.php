<?php

namespace SuperV\Platform\Domains\Task;

use Illuminate\Contracts\Queue\ShouldQueue;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;

class Deployer implements ShouldQueue
{
    use ServesFeaturesTrait;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var TaskResult
     */
    private $result;

    public function __construct(TaskResult $result)
    {
        $this->result = $result;
    }

    public function task(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    public function deploy()
    {
        $task = $this->task;

        $jobs = $this->serve($task->payload('feature'), ['params' => $task->payload()]);

        /*
         * First create sub tasks so that models
         * would be created in database
         */
        foreach ($jobs as $job) {
            $task->createSubTask($job);
        }

        try {
            $task->started();

            /** @var Job $job */
            foreach ($jobs as $job) {
                $subTask = $job->getTask();
                $subTask->started();
                try {
                    $this->dispatchNow($job);

                    $subTask->completed();
                } catch (\Exception $e) {
                    $subTask->failed($e->getMessage());

                    throw $e;
                }
            }

            $task->completed();
        } catch (\Exception $e) {
            $task->failed($e->getMessage());
        }
    }
}
