<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class FieldsList implements ListResolvedHook
{
    public static $identifier = 'sv.platform.fields.lists:default';

    public function resolved(TableInterface $table, IndexFields $fields)
    {
        $fields->get('handle')->searchable();
        $fields->get('resource')->copyToFilters();
        $fields->get('type')->copyToFilters();

        $table->setOption('limit', 50);
    }
}
