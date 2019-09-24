<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class EntryRepository implements EntryRepositoryInterface
{
    public function getEntry(string $identifier, int $id = null): ?EntryContract
    {
        if (is_null($id)) {
            [$identifier, $id] = explode(':', $identifier);
        }
        return ResourceFactory::make($identifier)->newQuery()->find($id);
    }

    public function create(string $identifier, array $attributes = [])
    {
        $resource = ResourceFactory::make($identifier);

        return $resource->create($attributes);
    }

    public function update(string $identifier, array $attributes = [])
    {
        $identifier = sv_identifier($identifier);

        $resource = ResourceFactory::make($identifier->getParent());

        $entry = $resource->find($identifier->id());

        $entry->update($attributes);
    }
}
