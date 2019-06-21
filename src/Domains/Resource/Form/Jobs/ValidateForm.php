<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormField;
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
                            ->map(function (FormField $field) {
                                return [$field->getIdentifier(), $this->parseFieldRules($field)];
                            })->filter()
                              ->toAssoc()
                              ->all();

        $attributes = $this->form->getFields()
                            ->map(function (FormField $field) {
                                return [$field->getIdentifier(), $field->getLabel()];
                            })->filter()
                            ->toAssoc()
                            ->all();


        $validator->make($this->data, $rules,[], $attributes);
    }

    private function parseFieldRules(FormField $field)
    {
        $rules = $field->base()->getRules();

        if ($field->base()->isRequired()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        return $rules;
    }
}
