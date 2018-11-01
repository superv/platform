<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Resource\ResourceModel;

class SyncField
{
    public function handle($event)
    {
        $column = $event->column;

        if ($column->autoIncrement || $column->type === 'timestamp') {
            return;
        }

        if (isset($event->model)) {
            $resourceEntry = ResourceModel::withModel($event->model);
        }
        if (! isset($resourceEntry)) {
            $resourceEntry = ResourceModel::withSlug($event->table);
        }

        if (! $resourceEntry) {
            throw new \Exception("Resource model entry not found for table [{$event->table}]");
        }

        if ($resourceEntry->hasField($column->name)) {
            $field = $resourceEntry->getField($column->name);
        } else {
            $field = $resourceEntry->createField($column->name);
        }

        $field->sync($column);
    }
}