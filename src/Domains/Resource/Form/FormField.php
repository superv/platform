<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Contracts\Filter\ProvidesField;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

class FormField implements ProvidesField
{
    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field */
    protected $base;

    /** @var \SuperV\Platform\Domains\Resource\Form\FieldLocation */
    protected $location;

    public function __construct(Field $field)
    {
        $this->base = $field;
    }

    public function isHidden()
    {
        return $this->base->isHidden();
    }

    public function makeField(): Field
    {
        return $this->base();
    }

    public function base(): Field
    {
        return $this->base;
    }

    public function getLocation(): ?FieldLocation
    {
        return $this->location;
    }

    public function setLocation(FieldLocation $location): void
    {
        $this->location = $location;
    }

    public function setForm(Form $form): FormField
    {
        $this->form = $form;

        return $this;
    }
}