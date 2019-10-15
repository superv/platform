<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

interface ListQueryResolvedHook
{
    /**
     * Callback to process after the query object for list is resolved
     *
     * @param \Illuminate\Database\Eloquent\Builder                            $query
     * @param \SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface $table
     */
    public function queryResolved($query, TableInterface $table);
}
