<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface BeforeCreatingHook
{
    public function creating(EntryContract $entry);
}
