<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;

interface InlinesForm
{
    public function inlineForm(FormInterface $parent): void;
}