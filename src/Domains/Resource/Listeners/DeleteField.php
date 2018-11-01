<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Resource\ResourceModel;

class DeleteField
{
    public function handle(ColumnDroppedEvent $event)
    {
        if (! $resourceEntry = ResourceModel::withSlug($event->table)) {
            return;
        }

        if ($field = $resourceEntry->getField($event->columnName)) {
            $field->delete();
        }
    }
}