<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\FormField as FormFieldContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form;

class FormField extends Field implements FormFieldContract
{
    /** @var Form */
    protected $form;

    public function getForm(): Form
    {
        return $this->form;
    }

    public function setForm(Form $form): void
    {
        $this->form = $form;
    }
}