<?php namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Modules\Supreme\Domains\Server\Model\ServerModel;
use SuperV\Platform\Domains\Entry\EntryModel;

class TaskModel extends EntryModel
{
    const COMPLETED = 0;
    const PENDING = 1;
    const RUNNING = 2;
    const FAILED = 3;
    const COMPLETED_WITH_ERRORS = 4;
    const ABORTING = 5;
    const ABORTED = 6;

    protected $table = 'platform_tasks';

    protected $casts = [
        'payload' => 'json',
    ];

    public function server()
    {
        return $this->hasOne(ServerModel::class, 'id', 'server_id');
    }

    public function getServer()
    {
        return $this->server;
    }

    public function appendOutput($buffer)
    {
        $this->update([
            'output' => $buffer . $this->output,
        ]);
    }
}