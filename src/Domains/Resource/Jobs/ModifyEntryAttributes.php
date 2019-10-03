<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use Closure;
use SuperV\Platform\Domains\Resource\Database\Entry\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Field\Modifier;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ModifyEntryAttributes
{
    public function handle(EntrySavingEvent $event)
    {
        $entry = $event->entry;

        if (! Resource::exists($entry)) {
            return;
        }

        $resource = ResourceFactory::make($entry);

        $resource->getFields()->map(function (FieldInterface $field) use ($entry) {
            if ($field->getFieldType() instanceof HasModifier) {
                $value = (new Modifier($field->getFieldType()))
                    ->set([
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