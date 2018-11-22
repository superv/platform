<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsEntry;
use SuperV\Platform\Support\Composition;

class EditEntryAction extends Action implements AcceptsEntry
{
    protected $name = 'edit';

    protected $title = 'Edit';

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function onComposed(Composition $composition)
    {
        $composition->replace('url', sprintf('sv/res/%s/%s/edit', $this->entry->getTable(), $this->entry->getId()));
    }

    public function acceptEntry(EntryContract $entry)
    {
        $this->entry = $entry;
    }
}