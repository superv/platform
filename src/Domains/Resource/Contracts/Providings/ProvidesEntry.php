<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Providings;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface ProvidesEntry
{
    public function provideEntry(): EntryContract;
}