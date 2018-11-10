<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

interface ProvidesEntry
{
    public function getEntry(): ResourceEntry;
}