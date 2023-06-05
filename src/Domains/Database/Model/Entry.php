<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\Builder;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRouter;
use SuperV\Platform\Domains\Resource\Jobs\GetTableResource;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;

abstract class Entry extends Eloquent implements EntryContract
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var string */
    protected $resourceIdentifier;

    /** @var \SuperV\Platform\Domains\Resource\ResourceConfig */
    protected $resourceConfig;

    protected $guarded = [];

    public $timestamps = false;

    protected $relationKeys = [];

    public function setRelationKeys($relationKeys)
    {
        $this->relationKeys = $relationKeys;

        return $this;
    }

    public function getRelationKeys()
    {
        return $this->relationKeys;
    }

    /**
     *  <superV> Overriding this to catch dynamic relations </superV>
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function newQuery()
    {
        return parent::newQuery();
    }

    public function router(): EntryRouter
    {
        return new EntryRouter($this);
    }

    public function getForeignKey()
    {
        return $this->getResourceConfig()->getResourceKey().'_id';
    }

    public function getMorphClass()
    {
        return $this->getResourceIdentifier() ?: parent::getMorphClass();
    }

    public function getId()
    {
        return $this->getKey();
    }

    public function wasRecentlyCreated(): bool
    {
        return $this->wasRecentlyCreated;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function getEntryLabel()
    {
        return sv_parse($this->getResourceConfig()->getEntryLabel(), $this->toArray());
    }

    public function getResource()
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->getResourceConfig()->getIdentifier());
        }

        return $this->resource;
    }

    public function getResourceConfig()
    {
        if (! $this->resourceConfig) {
            $this->resourceConfig = ResourceConfig::find($this->getResourceIdentifier());
        }

        return $this->resourceConfig;
    }

    public function getResourceDsn()
    {
        return sprintf("%s@%s://%s", 'database', $this->getConnectionName(), $this->getTable());
    }

    public function getResourceIdentifier(): ?string
    {
        if (! $this->resourceIdentifier) {
            $this->resourceIdentifier = GetTableResource::dispatch($this->getTable(), $this->getConnection()->getName());
        }

        return $this->resourceIdentifier;
    }

    public function setResourceIdentifier(string $resourceIdentifier): void
    {
        $this->resourceIdentifier = $resourceIdentifier;
    }
}
