<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

interface FormValidatingHook
{
    public function validating(FormInterface $form);
}
