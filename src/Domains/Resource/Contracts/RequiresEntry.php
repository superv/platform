<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface RequiresEntry
{
    public function setEntry(EntryContract $entry);
}