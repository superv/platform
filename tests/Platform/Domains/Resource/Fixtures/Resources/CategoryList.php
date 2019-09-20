<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListDataHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\Table;

class CategoryList implements ListResolvedHook, ListConfigHook, ListDataHook
{
    public static $identifier = 'testing.categories.lists:default';

    public function resolved(Table $table)
    {
        $_SERVER['__hooks::list.resolved'] = $table->getDataUrl();
    }

    public function config(Table $table, IndexFields $fields)
    {
        $_SERVER['__hooks::list.config'] = [
            'table'  => $table,
            'fields' => $fields,
        ];

        $_SERVER['__hooks::list.config.calls'] = ($_SERVER['__hooks::list.config.calls'] ?? 0) + 1;
    }

    public function data(Table $table)
    {
        $_SERVER['__hooks::list.data'] = ['rows' => $table->getRows()];
    }
}
