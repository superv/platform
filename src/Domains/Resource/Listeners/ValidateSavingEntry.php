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

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntryModel */
    protected $entry;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(EntrySavingEvent $event)
    {
//        $this->entry = $event->entry;
        $form = $event->form ?? $event->entry->wrap()->build(); // bu ne yaa

//        $resource = $this->entry->getResource();
//        $resource = $this->entry->wrap()->build();

        $rules = $form->getFields()->map(function (FieldType $field) {
            if (! $field->hasFieldEntry()) {
                return null;
            }

            return [$field->getName(), Rules::of($field)->get()];
        })->filter()->toAssoc()->all();

        $data = $form->getFields()->map(function (FieldType $field) {
            return [$field->getName(), $field->getValueForValidation()];
        })->toAssoc()->all();

        $attributes = $form->getFields()->map(function (FieldType $field) {
            return [$field->getName(), $field->getLabel()];
        })->toAssoc()->all();

        $this->validator->make($data, $rules, [], $attributes);
    }
}