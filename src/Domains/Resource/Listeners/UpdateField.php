<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Resource\ResourceModel;

class UpdateField
{
    public function handle(ColumnUpdatedEvent $event)
    {
        $column = $event->column;

        if ($column->autoIncrement || $column->type === 'timestamp') {
            return;
        }

        if (! $resourceEntry = ResourceModel::withSlug($event->table)) {
            return;
        }

        $field = $resourceEntry->getField($column->name);
        $field->sync($column);
    }
}