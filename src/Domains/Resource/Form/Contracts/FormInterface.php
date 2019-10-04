<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Form\FormFieldCollection;

interface FormInterface
{
    public function resolve();

    public function validate();

    public function save();

    public function submit();

    public function getIdentifier();

    public function setIdentifier(string $identifier): FormInterface;

    public function setEntry(?EntryContract $entry): FormInterface;

    public function getEntry(): ?EntryContract;

    public function hasEntry(): bool;

    public function getField(string $name): ?FormFieldInterface;

    public function addField(FormFieldInterface $field);

    public function setFields(FormFieldCollection $fields);

    public function fields(): FormFieldCollection;

    public function isCreating();

    public function isUpdating();

    public function setRequest(?Request $request): FormInterface;

    public function setData(FormData $data): FormInterface;

    public function getData(): FormData;
}
