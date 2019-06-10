<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Support\Dispatchable;

class ValidateForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /**
     * @var array
     */
    protected $data;

    public function __construct(Form $form, array $data)
    {
        $this->form = $form;
        $this->data = $data;
    }

    public function handle(Validator $validator)
    {
        $rules = $this->form->getFields()
                            ->map(function (Field $field) {
                                return [$field->getColumnName(), $this->parseFieldRules($field)];
                            })->filter()
                              ->toAssoc()
                              ->all();

        $attributes = $this->form->getFields()
                            ->map(function (Field $field) {
                                return [$field->getColumnName(), $field->getLabel()];
                            })->filter()
                            ->toAssoc()
                            ->all();


        $validator->make($this->data, $rules,[], $attributes);
    }

    private function parseFieldRules(Field $field)
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