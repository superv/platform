<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;



interface Form
{
    public function save();

    public function uuid();

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
