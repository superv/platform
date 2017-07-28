<?php namespace SuperV\Platform\Domains\UI\Form\Features;

use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\Form\Action;
use SuperV\Platform\Domains\UI\Form\FieldType;
use SuperV\Platform\Domains\UI\Form\FormBuilder;

class BuildForm extends Feature
{
    /**
     * @var FormBuilder
     */
    private $builder;

    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function handle()
    {
        $entry = $this->builder->getEntry();
        $form = $this->builder->getForm();

        foreach ($entry->getFields() as $field => $config) {
            if (!is_array($config)) {
                $field = $config;
                $config = [];
            }
            $fieldParts = explode('|', $field);
            list($fieldName, $fieldType) = explode(':', array_shift($fieldParts));

            $rules = [];
            if (in_array('unique', $fieldParts)) {
                $rule = "unique:{$entry->getTable()},{$fieldName},{$entry->getId()},id";
                array_push($rules, $rule);
            }
            if (in_array('required', $fieldParts)) {
                array_push($rules, "required");
            }

            $form->addField(new FieldType($fieldName, $fieldType, $rules, $config));
        }

        $form->addAction(new Action(
            'save', 'submit', [
                'label' => 'Save',
                'attr'  => ['class' => 'btn-success'],
            ]
        ));
    }
}