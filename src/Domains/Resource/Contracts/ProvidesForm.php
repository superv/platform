<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

interface ProvidesForm
{
    public function makeForm($request = null): FormInterface;

    public function getFormTitle(): string;
}
