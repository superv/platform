<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;
use SuperV\Platform\Support\Identifier;

interface FormInterface
{
    public function identifier(): Identifier;

    public function compose(): Payload;

    public function setRequest(Request $request): FormInterface;

    public function handle(Request $request = null): FormInterface;

    public function submit();

    /**
     * Render the SPA Component from composed data
     *
     * @return \SuperV\Platform\Domains\UI\Components\ComponentContract
     */
    public function render(): ComponentContract;

    public function getEntryIds(): array;

    public function addEntry($identifier, $id = null): FormInterface;

    public function setFieldValue($key, $value): FormInterface;

    public function isValid(): bool;

    public function getField(string $fieldName): ?FormField;

    public function getFieldValue(string $fieldName);

    public function getFields(): FormFieldCollection;

    public function setFields(FormFieldCollection $fields): FormInterface;

    public function isSubmitted(): bool;

    public function getIdentifier(): string;

    public function getMethod();

    public function isMethod($method): bool;

    public function setMethod($method): FormInterface;

    public function setIdentifier(string $identifier): FormInterface;

    public function getUrl(): string;

    public function setUrl(string $url): FormInterface;

    public function fireEvent(string $eventName, $payload = null);

    public function getData();

    public function getDataValue($parent, $key);

    public function setData($data): FormInterface;

    public function setValid(bool $valid): void;

    public function getEntry(string $identifier);

    public function getResponse();

    public function getRequestEntries();

    public function getFormAction();
}
