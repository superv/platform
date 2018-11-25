<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Model\ResourceEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryFake;

trait RepoConcern
{
    public function newQuery()
    {
        return $this->newEntryInstance()->newQuery();
    }

    public function newEntryInstance()
    {
        if ($model = $this->getConfigValue('model')) {
            // Custom Entry Model
            $entry = new $model;
        } else {
            // Anonymous Entry Model
            $entry = ResourceEntry::make($this->getHandle());
        }

        return $entry;
    }

    public function create(array $attributes = []): EntryContract
    {
        return $this->newEntryInstance()->create($attributes);
    }

    public function find($id): ?EntryContract
    {
        if (! $entry = $this->newQuery()->find($id)) {
            return null;
        }

        return $entry;
    }

    public function first(): ?EntryContract
    {
        if (! $entry = $this->newQuery()->first()) {
            return null;
        }

        return $entry;
    }

    public function count(): int
    {
        return $this->newQuery()->count();
    }

    /** @return \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|array */
    public function fake(array $overrides = [], int $number = 1)
    {
        return ResourceEntryFake::make($this, $overrides, $number);
    }
}