<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;

interface FieldComposer
{
    public function toForm(Form $form, FormField $field);
}
