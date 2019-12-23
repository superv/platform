<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use SuperV\Platform\Domains\Resource\Contracts\DataMapInterface;

interface FieldValueInterface
{
    public function setField(FieldInterface $field): FieldValueInterface;

    public function resolve(): FieldValueInterface;

    public function get();

    public function set($value): FieldValueInterface;

    public function mapTo(DataMapInterface $dataMap): FieldValueInterface;

    public function setEntry(\SuperV\Platform\Domains\Database\Model\Contracts\EntryContract $entry
    ): FieldValueInterface;

    public function setRequest(\Illuminate\Http\Request $request): FieldValueInterface;
}