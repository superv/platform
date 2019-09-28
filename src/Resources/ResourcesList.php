<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class ResourcesList implements ListResolvedHook, ListConfigHook
{
    public static $identifier = 'platform.resources.lists:default';

    public function resolved(TableInterface $table)
    {
//        $table->getField('namespace')->copyToFilters();
    }

    public function config(TableInterface $table, IndexFields $fields)
    {
        $table->showIdColumn();
        $table->setOption('limit', 10);

        $fields->get('identifier')->searchable();
        $fields->get('namespace')->copyToFilters();
    }
}
