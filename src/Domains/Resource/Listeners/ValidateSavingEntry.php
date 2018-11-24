<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;

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
        $this->entry = $event->entry;

        if (! $this->entry->exists) {
            return;
        }

        $rules = [];

        $data = [];

        return;

        $attributes = $form->provideFields()->map(function (FieldType $field) {
            return [$field->getName(), $field->getLabel()];
        })->toAssoc()->all();

        $this->validator->make($data, $rules, [], $attributes);
    }
}