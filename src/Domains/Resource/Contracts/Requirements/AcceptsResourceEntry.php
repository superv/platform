<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Requirements;

use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;

interface AcceptsResourceEntry
{
    public function acceptResourceEntry(ResourceEntry $entry);
}