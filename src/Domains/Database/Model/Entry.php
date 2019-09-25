<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Jobs\GetEntryResource;
use SuperV\Platform\Domains\Resource\Model\Builder;
use SuperV\Platform\Domains\Resource\Model\EntryRouter;
use SuperV\Platform\Domains\Resource\Resource;
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

    /**
     * Create a new Eloquent query builder for the model.
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
//        if (optional($this->getResourceConfig())->isRestorable()) {
//            static::addGlobalScope(new SoftDeletingScope());
//        } else {
//            return parent::newQuery()->withoutGlobalScopes();
//        }

        return parent::newQuery();
    }

    protected function newBaseQueryBuilder_xxxxxx()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
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

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public function getResource()
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->getResourceConfig()->getIdentifier());
        }

        return $this->resource;
    }

    public function setResource(Resource $resource): EntryContract
    {
        $this->resource = $resource;

        return $this;
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
            $this->resourceIdentifier = GetEntryResource::dispatch($this);
        }

        return $this->resourceIdentifier;
    }

    public function setResourceIdentifier(string $resourceIdentifier): void
    {
        $this->resourceIdentifier = $resourceIdentifier;
    }
}
