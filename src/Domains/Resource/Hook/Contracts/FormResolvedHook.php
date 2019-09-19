<?php

namespace SuperV\Platform\Domains\Resource\Hook\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\Form;

interface FormResolvedHook
{
    public function resolved(Form $form);
}
