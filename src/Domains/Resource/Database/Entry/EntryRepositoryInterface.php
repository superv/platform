<?php

namespace SuperV\Platform\Domains\Resource\Database\Entry;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface EntryRepositoryInterface
{
    public function getEntry(string $identifier, int $id = null): ?EntryContract;

    public function create(array $attributes = []);

    public function update(string $identifier, array $attributes = []);

    public function newInstance(): EntryContract;

    public function count(): int;

    public function newQuery(): \Illuminate\Database\Eloquent\Builder;

    public function hydrateEntry(EntryContract $entry);

    public function first(): ?EntryContract;

    public function find($id): ?EntryContract;

    public function setResource($resource): EntryRepositoryInterface;
}
