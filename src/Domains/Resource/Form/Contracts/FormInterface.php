<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Form\FormFields;
use SuperV\Platform\Domains\Resource\Form\FormResponse;
use SuperV\Platform\Support\Composer\Payload;

interface FormInterface
{
    public function resolve(): FormInterface;

    public function resolveRequest(?Request $request = null): FormInterface;

    public function resolveEntry(): FormInterface;

    public function validate();

    public function save(): FormResponse;

    public function submit();

    public function compose(): Payload;

    public function fireEvent($event, array $payload = []);

    public function getIdentifier();

    public function setIdentifier(string $identifier): FormInterface;

    public function setEntry(?EntryContract $entry): FormInterface;

    public function getEntry(): ?EntryContract;

    public function hasEntry(): bool;

    public function getField(string $name): ?FormFieldInterface;

    public function addField(FormFieldInterface $field);

    public function setFields(FormFields $fields);

    public function fields(): FormFields;

    public function getFieldRpcUrl($fieldHandle, $rpcKey);

    public function isPublic();

    public function isCreating();

    public function isUpdating();

    public function setRequest(?Request $request): FormInterface;

    public function setData($data): FormInterface;

    public function getData(): FormData;

    public function setUrl(string $url): FormInterface;

    public function getActions(): array;

    public function getUrl();

    public function setActions(array $actions): void;

    public function setPublic(bool $public): void;

    public function getRequest(): ?Request;
}
