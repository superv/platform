<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

interface ObservesSaving
{
    public function saving(ResourceEntry $entry);
}