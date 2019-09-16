<?php

namespace SuperV\Platform\Domains\UI\Components;

use SuperV\Platform\Domains\Resource\Form\EntryForm;

class FormComponent extends Component
{
    protected $name = 'sv-form';

    /** @var \SuperV\Platform\Domains\Resource\Form\EntryForm */
    protected $form;

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
