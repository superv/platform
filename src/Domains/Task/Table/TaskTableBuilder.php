<?php

namespace SuperV\Platform\Domains\Task\Table;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Domains\Entry\EntryModel;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class TaskTableBuilder extends TableBuilder
{
    protected $buttons = [];

    public function getColumns()
    {
        return [
            'id',
            'title',
            'status' => 'entry.statusLabel()'
        ];
    }

    public function onQuerying(Builder $query)
    {
        $query->where('parent_id', null)->orderBy('id', 'DESC');
    }
}