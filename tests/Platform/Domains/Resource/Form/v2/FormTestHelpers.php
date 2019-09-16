<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\v2\Factory;

trait FormTestHelpers
{
    protected function makeTestFields()
    {
        return [
            ['identifier' => 'name', 'type' => 'text'],
            ['identifier' => 'email', 'type' => 'email'],
        ];
    }

    protected function makeFormBuilder(array $fields = []): FormBuilder
    {
        $builder = Factory::createBuilder();

        if (! empty($fields)) {
            foreach ($fields as $field) {
                $builder->addField(FormField::make($field));
            }
        }

        $builder->setFormIdentifier(uuid());

        return $builder;
    }
}
