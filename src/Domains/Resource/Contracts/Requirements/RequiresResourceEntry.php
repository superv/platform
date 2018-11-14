<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Requirements;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

interface RequiresResourceEntry
{
    public function setResourceEntry(ResourceEntry $entry);
}