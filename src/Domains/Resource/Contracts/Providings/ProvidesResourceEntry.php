<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Providings;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

interface ProvidesResourceEntry
{
    public function provideResourceEntry(): ResourceEntry;
}