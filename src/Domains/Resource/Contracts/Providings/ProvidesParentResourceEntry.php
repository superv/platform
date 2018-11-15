<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Providings;


use SuperV\Platform\Domains\Resource\Model\Contracts\ResourceEntry;

interface ProvidesParentResourceEntry
{
    public function provideParentResourceEntry(): ResourceEntry;
}