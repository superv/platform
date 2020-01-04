<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class FormsList implements ListResolvedHook
{
    public static $identifier = 'sv.platform.forms.lists:default';

    public function resolved(TableInterface $table, IndexFields $fields)
    {
        $fields->get('identifier')->searchable();
    }
}
