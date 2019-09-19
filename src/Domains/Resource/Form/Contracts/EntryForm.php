<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface EntryForm extends Form
{
    public function setEntry(EntryContract $entry): \SuperV\Platform\Domains\Resource\Form\EntryForm;

    public function getEntry(): ?EntryContract;

    public function hasEntry(): bool;
}
