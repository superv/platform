<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\EntryForm;
use SuperV\Platform\Domains\UI\Components\BaseComponent;
use SuperV\Platform\Domains\UI\Components\Props;

class FormComponent extends BaseComponent
{
    protected $name = 'sv-form-v2';

    protected $form;

    public function __construct(Contracts\Form $form)
    {
        $this->form = $form;
    }

    public function getProps(): Props
    {
        return $this->props->merge($this->form->compose()->get());
    }

    public function uuid()
    {
        return $this->form->uuid();
    }

    public static function from(EntryForm $form): self
    {
        $static = new static;
        $static->form = $form;

        return $static;
    }
}
