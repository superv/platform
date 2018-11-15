<?php

namespace SuperV\Platform\Domains\Resource\Action;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\MustBeInitialized;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class DeleteEntryAction extends Action implements AcceptsResourceEntry, MustBeInitialized
{
    protected $name = 'delete';

    protected $title = 'Delete';

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $entry;

    public function acceptResourceEntry(ResourceEntry $entry)
    {
        $this->entry = $entry;
    }

    public function merge()
    {
        $this->payload['url'] = $this->entry->route('delete');
    }

    public function init()
    {
        $this->on('composed', Closure::fromCallable([$this, 'merge']));
    }
}