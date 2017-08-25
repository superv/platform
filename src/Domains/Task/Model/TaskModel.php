<?php namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Modules\Supreme\Domains\Server\Model\ServerModel;

class TaskModel extends TaskEntryModel
{
    protected $casts = [
        'payload' => 'json',
    ];

    public function getTitle()
    {
        return $this->title;
    }

    public function server()
    {
        return $this->hasOne(ServerModel::class, 'id', 'server_id');
    }
    
    public function getStatus()
    {
        return $this->status;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function subtasks()
    {
        return $this->hasMany(TaskModel::class, 'parent_id');
    }

    public function getSubTasks()
    {
        return $this->subtasks;
    }

    public function appendOutput($buffer)
    {
        $this->update([
            'output' => $buffer . $this->output,
        ]);
    }
}