<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

interface ComposerInterface
{
    public function compose(): Payload;

    public function toForm(FormInterface $form = null): Payload;

    public function toView(EntryContract $entry): Payload;

    public function toTable(?EntryContract $entry = null): Payload;
}