<?php

namespace SuperV\Platform\Domains\Resource\Contracts;

use SuperV\Platform\Domains\Resource\Form\EntryForm;

interface ProvidesForm
{
    public function makeForm(): EntryForm;

    public function getFormTitle(): string;
}
