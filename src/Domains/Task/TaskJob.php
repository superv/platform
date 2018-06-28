<?php

namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Entry\EntryModel;

class TaskJob extends EntryModel
{
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}