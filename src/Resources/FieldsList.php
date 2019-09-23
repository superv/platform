<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\Table;

class FieldsList implements ListConfigHook
{
    public static $identifier = 'platform.fields.lists:default';

    public function config(Table $table, IndexFields $fields)
    {
        $fields->get('name')->searchable();
        $fields->get('resource')->copyToFilters();
        $fields->get('type')->copyToFilters();

        $table->setOption('limit', 50);
    }
}
