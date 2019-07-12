<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Form\FieldLocation;

interface FormField extends Field
{
    public function observe(FormField $parent, ?EntryContract $entry = null);

    public function getForm(): Form;

    public function setForm(Form $form): void;

    public function getLocation(): ?FieldLocation;

    public function setLocation(FieldLocation $location): void;

    public function setTemporal($temporal);

    public function isTemporal(): bool;
}