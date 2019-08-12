<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;


interface Form
{
    public function save();

    public function uuid();

    public function hideField(string $fieldName): Form;
}
