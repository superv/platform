<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormFields;

interface FormResolvingHook
{
    public function resolving(FormInterface $form, FormFields $fields);
}
