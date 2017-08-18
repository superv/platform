<?php namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Platform\Domains\Entry\EntryModel;

class JobModel extends JobEntryModel
{

    public function appendOutput($buffer)
    {
        $this->update([
            'output' => $buffer . $this->output,
        ]);
    }

}