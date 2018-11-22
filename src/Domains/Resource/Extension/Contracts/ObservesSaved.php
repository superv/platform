<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

interface ObservesSaved
{
    public function saved(ResourceEntry $entry);
}