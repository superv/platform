<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsEntry;
use SuperV\Platform\Support\Composer\Composition;

class DeleteEntryAction extends Action implements AcceptsEntry
{
    protected $name = 'delete';

    protected $title = 'Delete';

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', sprintf('sv/res/%s/%s/delete', $this->entry->getTable(), $this->entry->getId()));
    }

    public function acceptEntry(EntryContract $entry)
    {
        $this->entry = $entry;
    }
}