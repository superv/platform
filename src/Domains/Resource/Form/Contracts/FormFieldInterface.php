<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Form\FieldLocation;

interface FormFieldInterface extends FieldInterface
{
    public function observe(FormFieldInterface $parent, ?EntryContract $entry = null);

    public function getForm(): FormInterface;

    public function setForm(FormInterface $form): FormFieldInterface;

    public function getLocation(): ?FieldLocation;

    public function setLocation(FieldLocation $location): FormFieldInterface;

    public function setTemporal($temporal);

    public function isTemporal(): bool;

    /**
     * Inline get underlying relation form
     *
     * @param array $config
     */
    public function inlineForm(array $config = []);
}