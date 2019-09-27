<?php

namespace SuperV\Platform\Domains\Resource\Database\Entry;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class EntryRepository implements EntryRepositoryInterface
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function getEntry(string $identifier, int $id = null): ?EntryContract
    {
        if (is_null($id)) {
            [$identifier, $id] = explode(':', $identifier);
        }

        return ResourceFactory::make($identifier)->newQuery()->find($id);
    }

    public function create(array $attributes = [])
    {
        $query = $this->newQuery();

        $entry = $query->create($attributes);

        return $this->hydrateEntry($entry);
    }

    public function update(string $identifier, array $attributes = [])
    {
        $identifier = sv_identifier($identifier);

        $resource = ResourceFactory::make($identifier->getParent());

        $entry = $resource->find($identifier->id());

        $entry->update($attributes);
    }

    public function count(): int
    {
        return $this->newQuery()->count();
    }

    public function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->newInstance()->newQuery()->with($this->resource->getWith());

        return $query;
    }

    public function newInstance(): EntryContract
    {
        // Custom Entry Model
        if ($model = $this->resource->config()->getModel()) {
            $entry = new $model;
        } // Anonymous Entry Model
        else {
            $entry = new AnonymousModel();
            $entry->setTable($this->resource->config()->getTable());
            $entry->setConnection($this->resource->config()->getConnection());
        }

        $this->hydrateEntry($entry);

        return $entry;
    }

    public function find($id): ?EntryContract
    {
        if (! $entry = $this->newQuery()->find($id)) {
            return null;
        }

        return $this->hydrateEntry($entry);
    }

    public function first(): ?EntryContract
    {
        if (! $entry = $this->newQuery()->first()) {
            return null;
        }

        return $this->hydrateEntry($entry);
    }

    public function hydrateEntry(EntryContract $entry)
    {
        $entry->setRelationKeys($this->resource->getRelations()->keys()->all());
        $entry->setKeyName($this->resource->getKeyName());
        $entry->setResourceIdentifier($this->resource->getIdentifier());

        $this->resource->getFields()->map(function (Field $field) use ($entry) {
            if ($value = $field->getConfigValue('default_value')) {
                $entry->setAttribute($field->getColumnName(), $value);
            }
        });

        return $entry;
    }

    public function setResource($resource): EntryRepositoryInterface
    {
        if (is_string($resource)) {
            $resource = ResourceFactory::make($resource);
        }

        $this->resource = $resource;

        return $this;
    }

    public static function for($resource): EntryRepositoryInterface
    {
        return static::resolve()->setResource($resource);
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
