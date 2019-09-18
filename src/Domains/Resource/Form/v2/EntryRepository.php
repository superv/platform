<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class EntryRepository implements EntryRepositoryInterface
{
    public function getEntry(string $identifier, int $id): ?EntryContract
    {
        return ResourceFactory::make($identifier)->newQuery()->find($id);
    }
}
