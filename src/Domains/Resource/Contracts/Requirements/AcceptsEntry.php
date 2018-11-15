<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Requirements;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface AcceptsEntry
{
    public function acceptEntry(EntryContract $entry);
}