<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Form\Form;

interface ProvidesForm
{
    public function makeForm(): Form;
}