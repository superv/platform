<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

interface SortsQuery
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $direction
     * @throws \Exception
     */
    public function sortQuery($query, $direction);
}
