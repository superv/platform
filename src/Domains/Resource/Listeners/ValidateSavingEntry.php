<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;
use SuperV\Platform\Domains\Resource\Resource;

class ValidateSavingEntry
{
    /**
     * @var \SuperV\Platform\Contracts\Validator
     */
    protected $validator;

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $entry;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(EntrySavingEvent $event)
    {
        return;
        $this->entry = $event->entry;

        if (starts_with($this->entry->getTable(), 'sv_')) {
            return;
        }
//
//        if (! starts_with($this->entry->getTable(), 't_')) {
//            return;
//        }

        if (! $resource = Resource::of($this->entry)) {
            return;
        }

        $rules = $resource->getRules($this->entry);

        $data = $this->entry->exists ? $this->entry->getChanges() : $this->entry->toArray();

        $this->validator->make($data, $rules, []);
    }
}