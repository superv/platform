<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Model\Events\EntrySavingEvent;

class ValidateSavingEntry
{
    /**
     * @var \SuperV\Platform\Contracts\Validator
     */
    protected $validator;

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel */
    protected $entry;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(EntrySavingEvent $event)
    {
//        $this->entry = $event->entry;
        $form = $event->form;

//        $resource = $this->entry->getResource();
//        $resource = $this->entry->wrap()->build();

        $rules = $form->getFields()->map(function (Field $field) {
            if (! $field->hasEntry()) {
                return null;
            }

            return [$field->getName(), Rules::of($field)->get()];
        })->filter()->toAssoc()->all();

        $data = $form->getFields()->map(function (Field $field) {
            return [$field->getName(), $field->getValueForValidation()];
        })->toAssoc()->all();

        $attributes = $form->getFields()->map(function (Field $field) {
            return [$field->getName(), $field->getLabel()];
        })->toAssoc()->all();

        $this->validator->make($data, $rules, [], $attributes);
    }
}