<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Jobs\GetEntryResource;

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
