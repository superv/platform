<?php

namespace SuperV\Platform\Domains\Resource\Relation\Actions;

use SuperV\Platform\Domains\Resource\Table\Actions\SelectionAction;

class AttachEntriesAction extends SelectionAction
{
    protected $name = 'attach_selected';

    protected $title = 'Attach Selectedxxxx';

    public function handle($query)
    {
        sv_debug($query->count());
    }
}