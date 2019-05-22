<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface ObservesEntrySaving
{
    public function saving(EntryContract $entry);
}