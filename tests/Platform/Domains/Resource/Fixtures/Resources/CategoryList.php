<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListDataHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListQueryResolvedHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class CategoryList implements ListResolvedHook, ListDataHook, ListQueryResolvedHook
{
    public static $identifier = 'sv.testing.categories.lists:default';

    public function resolved(TableInterface $table, IndexFields $fields)
    {
        $_SERVER['__hooks::list.resolved'] = $table->getDataUrl();
    }

    public function data(TableInterface $table)
    {
        $_SERVER['__hooks::list.data'] = ['rows' => $table->getRows()];
    }

    public function queryResolved($query, TableInterface $table)
    {
        $_SERVER['__hooks::list.query.resolved'] = $query;
    }
}
