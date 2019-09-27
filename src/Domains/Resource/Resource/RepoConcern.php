<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntryFake;
use SuperV\Platform\Domains\Resource\Fake;

/**
 * Trait RepoConcern
 * @method \SuperV\Platform\Domains\Resource\ResourceConfig config()
 *
 * @package SuperV\Platform\Domains\Resource\Resource
 * @property \SuperV\Platform\Domains\Resource\Database\Entry\EntryRepositoryInterface $entryRepository
 */
trait RepoConcern
{
    protected $with = [];

    public function getWith(): array
    {
        return $this->with;
    }

    public function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->entryRepository->newQuery();
    }

    public function with($relation)
    {
        $this->with[] = $relation;

        return $this;
    }

    public function newEntryInstance()
    {
        return $this->entryRepository->newInstance();
    }

    public function create(array $attributes = []): EntryContract
    {
        return $this->entryRepository->create($attributes);
    }

    public function find($id): ?EntryContract
    {
        return $this->entryRepository->find($id);
    }

    public function first(): ?EntryContract
    {
        return $this->entryRepository->first();
    }

    public function count(): int
    {
        return $this->entryRepository->count();
    }

    /** @return  \SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry|array */
    public function fake(array $overrides = [], int $number = 1, Closure $callback = null)
    {
        return ResourceEntryFake::make($this, $overrides, $number, $callback);
    }

    /** @return \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract|array */
    public function fakeMake(array $overrides = [], int $number = 1)
    {
        if ($number > 1) {
            return collect(range(1, $number))
                ->map(function () use ($overrides) {
                    return Fake::make($this, $overrides);
                })
                ->all();
        }

        return Fake::make($this, $overrides);
    }
}
