<?php namespace SuperV\Platform\Domains\Task;

interface TaskJob
{
    public function setListener(TaskListener $listener);
}