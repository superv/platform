<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;

interface ObservesSaving
{
    public function saving(ResourceEntryModel $entry);
}