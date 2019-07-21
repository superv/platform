<?php

namespace SuperV\Platform\Domains\Resource\Contracts\Hook;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;

interface AfterSavedHook
{
    public function after(EntryContract $entry, Resource $resource);
}