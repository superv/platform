<?php

namespace SuperV\Platform\Domains\UI\DeprecatedForm\Features;

use SuperV\Platform\Domains\UI\DeprecatedForm\Action;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\UI\DeprecatedForm\FieldType;
use SuperV\Platform\Domains\UI\DeprecatedForm\FormBuilder;

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
            if (! is_array($config)) {
                $field = $config;
                $config = [];
            }
            $fieldParts = explode('|', $field);
            list($fieldName, $fieldType) = explode(':', array_shift($fieldParts));

            $rules = [];
            foreach ($fieldParts as $part) {
                if ($part == 'unique') {
                    $rule = "unique:{$entry->getTable()},{$fieldName},{$entry->getId()},id";
                    array_push($rules, $rule);
                } elseif ($part == 'required') {
                    array_set($config, 'required', true);
                } else {
                    array_push($rules, $part);
                }
            }

            $form->addField(new FieldType($entry, $fieldName, $fieldType, $rules, $config));
        }

        $form->addAction(new Action(
            'save', 'submit', [
                'label' => 'Save',
                'attr'  => ['class' => 'btn-success'],
            ]
        ));
    }
}
