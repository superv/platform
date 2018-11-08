<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Model\Entry;

interface NeedsEntry
{
    public function setEntry(Entry $entry);
}