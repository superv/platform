<?php

namespace SuperV\Platform\Domains\Nucleo\Resource;

use SuperV\Platform\Contracts\Dispatcher;

class ResourceEntryObserver
{
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function creating(ResourceEntry $entry)
    {
    }

    public function created(ResourceEntry $entry)
    {
        if ($callback = $entry->getOnCreateCallback()) {
            return call_user_func($callback, $entry);
        }
    }

    public function updating(ResourceEntry $entry)
    {
    }

    public function updated(ResourceEntry $entry)
    {
    }

    public function saving(ResourceEntry $entry)
    {
        $rules = [];
        $attributes = [];
        $data = [];

        /** @var \SuperV\Platform\Domains\Nucleo\Field $field */
        foreach ($entry->prototype()->fields as $field) {
            if ($field->slug === $entry->getKeyName()) {
                continue;
            }

            if ($field->hasRules()) {
                $rules[$field->slug] = $field->getRules();
                $attributes[$field->slug] = sprintf('%s.%s', $entry->getTable(), $field->slug);
                $data[$field->slug] = $entry->getAttribute($field->slug);
            }
        }

        if (! empty($rules)) {
            $validator = validator($data, $rules, [], $attributes);
            $validator->validate();
        }
    }

    public function saved(ResourceEntry $entry) { }

    public function deleting(ResourceEntry $entry)
    {
    }

    public function deleted(ResourceEntry $entry)
    {
    }

    public function deletedMultiple(ResourceEntry $entry)
    {
    }
}