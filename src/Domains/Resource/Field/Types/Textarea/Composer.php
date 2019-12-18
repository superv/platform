<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Textarea;

use SuperV\Platform\Domains\Resource\Field\Composer as BaseComposer;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

class Composer extends BaseComposer
{
    public function form(?FormInterface $form = null): void
    {
        if ($this->getConfigValue('rich') === true) {
            $this->payload->set('meta.rich', true);
        }
    }
}