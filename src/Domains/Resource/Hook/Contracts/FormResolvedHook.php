<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

interface FormResolvedHook
{
    public function resolved(FormInterface $form);
}
