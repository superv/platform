<?php

namespace SuperV\Platform\Domains\Resource\Action;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\MustBeInitialized;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class EditEntryAction extends Action implements AcceptsResourceEntry, MustBeInitialized
{
    protected $name = 'edit';

    protected $title = 'Edit';

    /** @var \SuperV\Platform\Domains\Database\Model\Entry */
    protected $entry;

    public function acceptResourceEntry(ResourceEntry $entry)
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