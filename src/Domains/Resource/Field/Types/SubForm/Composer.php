<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\SubForm;

use SuperV\Platform\Domains\Resource\Field\FieldComposer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    public function form(?FormInterface $form = null): void
    {
        $this->payload->set('config.fields', $this->fieldType->getFormComponent());
        $this->payload->set('meta.full', true);
    }
}