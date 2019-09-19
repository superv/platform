<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Support\Dispatchable;

class ValidateForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\EntryForm
     */
    protected $form;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    public function __construct(Collection $fields, array $data)
    {
        $this->data = $data;
        $this->fields = $fields;
    }

    public function handle(Validator $validator)
    {
        $rules = $this->fields
            ->filter(function (Field $field) {
                return ! $field->isUnbound();
            })
            ->map(function (FormField $field) {
                return [$field->getIdentifier(), $this->parseFieldRules($field)];
            })->filter()
            ->toAssoc()
            ->all();

        $attributes = $this->fields
            ->map(function (FormField $field) {
                return [$field->getIdentifier(), $field->getLabel()];
            })->filter()
            ->toAssoc()
            ->all();

        $validator->make($this->data, $rules, [], $attributes);
    }

    private function parseFieldRules(FormField $field)
    {
        $rules = $field->getRules();

        if ($field->isRequired()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        return $rules;
    }
}
