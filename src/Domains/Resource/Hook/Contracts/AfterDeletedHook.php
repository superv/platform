<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface AfterDeletedHook
{
    public function deleted(EntryContract $entry);
}
