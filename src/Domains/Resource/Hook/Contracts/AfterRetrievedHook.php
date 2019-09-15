<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface AfterRetrievedHook
{
    public function retrieved(EntryContract $entry);
}
