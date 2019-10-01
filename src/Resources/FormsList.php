<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class FormsList implements ListConfigHook
{
    public static $identifier = 'platform.forms.lists:default';

    public function config(TableInterface $table, IndexFields $fields)
    {
        $fields->get('identifier')->searchable();
    }
}
