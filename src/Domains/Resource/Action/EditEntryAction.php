<?php

namespace SuperV\Platform\Domains\Resource\Action;

use Closure;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Resource\Contracts\MustBeInitialized;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\RequiresResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class EditEntryAction extends Action implements RequiresResourceEntry, MustBeInitialized
{
    protected $name = 'edit';

    protected $title = 'Edit';

    /** @var \SuperV\Platform\Domains\Database\Model\Entry */
    protected $entry;


    public function setResourceEntry(ResourceEntry $entry)
    {
        $this->entry = $entry;
    }

    public function merge()
    {
        $this->payload['url'] = $this->entry->route('edit');
    }

    public function init()
    {
        $this->on('composed', Closure::fromCallable([$this, 'merge']));
    }
}