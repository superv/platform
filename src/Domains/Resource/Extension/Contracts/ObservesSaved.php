<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;

interface ObservesSaved
{
    public function saved(ResourceEntryModel $entry);
}