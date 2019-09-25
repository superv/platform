<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use Closure;
use Platform;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntryRetrievedEvent;
use SuperV\Platform\Domains\Resource\Field\Accessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class AccessEntryAttributes
{
    public function handle(EntryRetrievedEvent $event)
    {
        $entry = $event->entry;

        if (starts_with($entry->getTable(), 'sv_')) {
            return;
        }

        if (! Platform::isInstalled()) {
            return;
        }

        if (! Resource::exists($entry)) {
            return;
        }

        $resource = ResourceFactory::make($entry);

        $resource->getFields()->map(function (Field $field) use ($entry) {
            if ($field->getFieldType() instanceof HasAccessor) {
                $value = (new Accessor($field->getFieldType()))
                    ->get([
                        'entry' => $entry,
                        'value' => $entry->getAttribute($field->getColumnName()),
                    ]);

                if ($value instanceof Closure) {
                    return;
                }

                $entry->setAttribute($field->getColumnName(), $value);
            }
        });
    }
}