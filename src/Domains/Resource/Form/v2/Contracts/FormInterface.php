<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

interface FormInterface
{
    public function compose(): Payload;

    public function handle(Request $request);

    /**
     * Render the SPA Component from composed data
     *
     * @return \SuperV\Platform\Domains\UI\Components\ComponentContract
     */
    public function render(): ComponentContract;

    public function getEntryIds(): array;

    public function addEntry($identifier, $id);

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

    public function getDataValue($parent, $key);
}
