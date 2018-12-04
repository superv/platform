<?php

namespace SuperV\Platform\Domains\Database\Model;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

class Entry extends Model implements EntryContract
{
    protected $guarded = [];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
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

//    public function getOwnerType()
//    {
//        return $this->getMorphClass();
//    }
//
//    public function getOwnerId()
//    {
//        return $this->getKey();
//    }

    public function getId()
    {
        return $this->attributes['id'] ?? null;
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