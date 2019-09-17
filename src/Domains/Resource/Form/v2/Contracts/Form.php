<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Support\Composer\Payload;

interface Form
{
    public function compose(): Payload;

    public function handle(Request $request);

    public function submit($data);

    /**
     * Render the SPA Component from composed data
     *
     * @return \SuperV\Platform\Domains\UI\Components\ComponentContract
     */
    public function render(): ComponentContract;

    public function setData($data): Form;

    public function setFieldValue($key, $value): Form;

    public function isValid(): bool;

    public function getField(string $fieldName): ?FormField;

    public function getFieldValue(string $fieldName);

    public function getFields(): FormFieldCollection;

    public function setFields(FormFieldCollection $fields): Form;

    public function isSubmitted(): bool;

    public function getIdentifier(): string;

    public function getMethod();

    public function setIdentifier(string $identifier): Form;

    public function getUrl(): string;

    public function setUrl(string $url): Form;
}
