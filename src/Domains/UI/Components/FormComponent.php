<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\Resource\Form\Form;

class FormComponent extends BaseComponent
{
    protected $name = 'sv-form';

    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    public function getProps(): Props
    {
        return $this->props->merge($this->form->compose()->get());
    }

    public function uuid()
    {
        return $this->form->getIdentifier();
    }

    public static function from(Form $form): self
    {
        $static = new static;
        $static->form = $form;

        return $static;
    }
}
