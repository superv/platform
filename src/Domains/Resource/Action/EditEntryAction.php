<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsResourceEntry;
use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;
use SuperV\Platform\Support\Composition;

class EditEntryAction extends Action implements AcceptsResourceEntry
{
    protected $name = 'edit';

    protected $title = 'Edit';

    /** @var \SuperV\Platform\Domains\Database\Model\Entry */
    protected $entry;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', $this->entry->route('edit'));
    }

    public function acceptResourceEntry(ResourceEntry $entry)
    {
        $this->entry = $entry;
    }
}