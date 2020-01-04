<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\TableInterface;

class TasksList implements ListResolvedHook
{
    public static $identifier = 'sv.platform.tasks.lists:default';

    public function resolved(TableInterface $table, IndexFields $fields)
    {
        $fields->show('status');
        $fields->show('created_at');

        $table->orderByLatest();
    }
}
