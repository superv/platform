<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;

interface Form
{
    public function save();

    public function uuid();

    public function getIdentifier();

    public function setEntry(EntryContract $entry): \SuperV\Platform\Domains\Resource\Form\Form;

    public function getEntry(): ?EntryContract;

    public function hasEntry(): bool;

    public function hideField(string $fieldName): Form;

    public function getField(string $name): ?FormField;

    public function hideFields($fields): Form;

    public function getHiddenFields(): array;

    public function mergeFields($fields);

    public function addField(FormField $field);

    public function setFields($fields);

    public function getFields();

    public function composeField($field, $entry = null);
}
