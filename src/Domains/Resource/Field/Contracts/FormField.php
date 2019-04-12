<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\Form;

interface FormField
{
    public function getForm(): Form;

    public function setForm(Form $form): void;
}