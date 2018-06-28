<?php

namespace SuperV\Platform\Domains\Task\Features;

use SuperV\Platform\Domains\Feature\AbstractFeature;
use SuperV\Platform\Domains\Task\Task;

class CreateTask extends AbstractFeature
{
    protected $title;

    protected $jobs;

    /** @var Task */
    protected $task;

    public function run()
    {
        $this->task = Task::create([
            'title' => $this->title,
            'description' => '',
            'owner_id' => 0,
            'owner_type' => '',
            'status' => 'pending',
        ]);

        foreach($this->jobs as $job) {
            $this->task->jobs()->create([
                'status' => 'pending'
            ]);
        }
    }

    public function getResponseData()
    {
        return [
            'data' => [
            ],
        ];
    }
}