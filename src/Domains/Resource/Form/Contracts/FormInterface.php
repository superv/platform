<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Form\FormFields;
use SuperV\Platform\Domains\Resource\Form\FormResponse;

interface FormInterface
{
    public function resolve(): FormInterface;

    public function validate();

    public function save(): FormResponse;

    public function submit();

    public function getIdentifier();

    public function setIdentifier(string $identifier): FormInterface;

    public function setEntry(?EntryContract $entry): FormInterface;

    public function getEntry(): ?EntryContract;

    public function hasEntry(): bool;

    public function getField(string $name): ?FormFieldInterface;

    public function addField(FormFieldInterface $field);

    public function setFields(FormFields $fields);

    public function fields(): FormFields;

    public function isCreating();

    public function isUpdating();

    public function setRequest(?Request $request): FormInterface;

    public function setData(FormData $data): FormInterface;

    public function getData(): FormData;

    public function setUrl(string $url): FormInterface;

    public function getActions(): array;

    public function getUrl();

    public function setActions(array $actions): void;
}
