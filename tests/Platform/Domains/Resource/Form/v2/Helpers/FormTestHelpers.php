<?php

namespace Tests\Platform\Domains\Resource\Form\v2\Helpers;

use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilderInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;

trait FormTestHelpers
{
    protected function makeTestFields()
    {
        return [
            $this->makeFieldArray('sv.users.fields:name', 'name'),
            $this->makeFieldArray('sv.users.fields:email', 'email'),
        ];
    }

    protected function makeFieldArray($identifier, $name, $type = 'text')
    {
        return compact('identifier', 'name', 'type');
    }

    protected function makeFormBuilder(array $fields = []): FormBuilderInterface
    {
        $builder = FormFactory::createBuilder();

        if (! empty($fields)) {
            foreach ($fields as $field) {
                $builder->addField(FormField::make($field));
            }
        }

        $builder->setFormIdentifier(uuid());

        return $builder;
    }

    protected function makeForm(array $fields = []): FormInterface
    {
        $builder = FormFactory::createBuilder();
        $builder->addFields($fields);
        $builder->setFormIdentifier($identifier = uuid());
        $form = $builder->getForm();

        return $form;
    }
}
