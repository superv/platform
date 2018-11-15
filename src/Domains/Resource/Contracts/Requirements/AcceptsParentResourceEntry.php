<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Requirements;


use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;

interface AcceptsParentResourceEntry
{
    public function acceptParentResourceEntry(ResourceEntry $entry);
}