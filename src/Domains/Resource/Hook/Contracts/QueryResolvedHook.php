<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

interface QueryResolvedHook
{
    /**
     * Callback to process after the query object is resolved
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function queryResolved($query);
}
