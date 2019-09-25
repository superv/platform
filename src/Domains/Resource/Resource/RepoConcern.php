<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\AnonymousModel;
use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntryFake;
use SuperV\Platform\Domains\Resource\Fake;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

trait RepoConcern
{
    protected $with = [];

    protected $scopes = [];

    public function addScope($scope)
    {
        $this->scopes[] = $scope;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $query = $this->newEntryInstance()->newQuery()->with($this->with);

        foreach ($this->scopes as $scope) {
            $scope($query);
        }

        return $query;
    }

    public function with($relation)
    {
        $this->with[] = $relation;

        return $this;
    }

    public function newEntryInstance()
    {
        if ($model = $this->config->getModel()) {
            // Custom Entry Model
            $entry = new $model;
        } else {
            // Anonymous Entry Model
//            $entry = ResourceEntry::make($this);

            $entry = new AnonymousModel();
            $entry->setTable($this->config()->getTable());
            $entry->setConnection($this->config()->getConnection());
            $entry->setKeyName($this->getKeyName());
            $entry->setResourceIdentifier($this->getIdentifier());
        }

        $this->getFields()->map(function (Field $field) use ($entry) {
            if ($value = $field->getConfigValue('default_value')) {
                $entry->setAttribute($field->getColumnName(), $value);
            }
        });

        return $entry;
    }

    public function create(array $attributes = []): EntryContract
    {
        $query = $this->newEntryInstance()->newQuery();

        return $query->create($attributes);
//        return $this->newEntryInstance()->setResource($this)->create($attributes);
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
