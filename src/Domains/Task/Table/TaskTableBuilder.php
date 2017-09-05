<?php

namespace SuperV\Platform\Domains\Task\Table;

use Illuminate\Database\Eloquent\Builder;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class TaskTableBuilder extends TableBuilder
{
    protected $buttons = [];

    public function getColumns(): ?array
    {
        return [
            'id',
            'title',
            'entry.statusLabel()',
            'info',
            'entry.subtasks().pluck("title")|join("<br>")',
        ];
    }

    public function onQuerying(?Builder $query): void
    {
        $query->where('parent_id', null)->orderBy('id', 'DESC');
    }
}