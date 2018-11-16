<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Support\Composition;

class DeleteEntryAction extends Action implements AcceptsResourceEntry
{
    protected $name = 'delete';

    protected $title = 'Delete';

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $entry;

    protected function boot()
    {
        parent::boot();

        $this->on('composed', function (Composition $composition) {
            $composition->replace('url', $this->entry->route('delete'));
        });
    }

    public function acceptResourceEntry(ResourceEntry $entry)
    {
        $this->entry = $entry;
    }
}