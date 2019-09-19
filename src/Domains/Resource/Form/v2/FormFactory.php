<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilderInterface;

class FormFactory
{
    public static function createBuilder(FormModel $formEntry = null): FormBuilderInterface
    {
        $builder = app(FormBuilderInterface::class);

        if ($formEntry) {
            $builder->setFormEntry($formEntry);
        }

        return $builder;
    }
}
