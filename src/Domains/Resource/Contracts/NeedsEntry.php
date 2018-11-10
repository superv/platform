<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

interface NeedsEntry
{
    public function setEntry(ResourceEntry $entry);
}