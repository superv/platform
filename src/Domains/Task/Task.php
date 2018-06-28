<?php
namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Entry\EntryModel;

class Task extends EntryModel
{
    public function jobs()
    {
        return $this->hasMany(TaskJob::class);
    }

    public function status()
    {
        return new TaskStatus($this->status);
    }

    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = $status instanceof TaskStatus ? (string)$status : $status;
    }
}