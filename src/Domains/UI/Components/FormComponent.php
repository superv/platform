<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\Resource\Form\Form;

class FormComponent extends BaseUIComponent
{
    protected $name = 'sv-form';

    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    public function getName(): string
    {
        return $this->name;
    }

    public function getProps(): Props
    {
        return $this->form->compose()->get();
    }

    public function uuid(): string
    {
        return $this->form->uuid();
    }

    public static function from(Form $form): self
    {
        $static = new static;
        $static->form = $form;

        return $static;
    }
}