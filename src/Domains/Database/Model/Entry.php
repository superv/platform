<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Jobs\GetEntryResource;
use SuperV\Platform\Domains\Resource\ResourceConfig;

abstract class Entry extends Eloquent implements EntryContract
{
    /**
     * @var string
     */
    protected $resourceIdentifier;

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
     * @return \SuperV\Platform\Domains\Database\Model\EloquentQueryBuilder|static
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentQueryBuilder($query);
    }

    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
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

    /**
     * @param $id
     * @return static
     */
    public static function find($id)
    {
        return static::query()->find($id);
    }
}
