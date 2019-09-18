<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface EntryRepositoryInterface
{
    public function getEntry(string $identifier, int $id): ?EntryContract;
}
