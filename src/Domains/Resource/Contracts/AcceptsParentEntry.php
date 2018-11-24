<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface AcceptsParentEntry
{
    public function acceptParentEntry(EntryContract $entry);
}