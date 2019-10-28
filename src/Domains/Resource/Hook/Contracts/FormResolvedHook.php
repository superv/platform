<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormFields;

interface FormResolvedHook
{
    public function resolved(FormInterface $form, FormFields $fields);
}
