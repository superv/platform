<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;

class OldFormField
{
    /** @var \SuperV\Platform\Domains\Resource\Form\Form */
    protected $form;

    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field */
    protected $base;

    /** @var \SuperV\Platform\Domains\Resource\Form\FieldLocation */
    protected $location;

    protected $temporal = false;

    public function __construct(Field $field)
    {
        $this->base = $field;
    }

    public function base(): Field
    {
        return $this->base;
    }

    public function setForm(Form $form): OldFormField
    {
        $this->form = $form;

        return $this;
    }


}
