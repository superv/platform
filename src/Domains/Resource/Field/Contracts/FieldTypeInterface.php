<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;

interface FieldTypeInterface
{
    public function getConfigValue($key, $default = null);

    public function getType(): ?string;

    public function saving(FormInterface $form);

    public function saved(FormInterface $form);

    public function setField(FieldInterface $field): void;

    public function getName();

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null);

    public function resolveDataFromEntry(FormData $data, EntryContract $entry);

    public function setConfig(array $config);

    public function addFlag($flag);

    public function getColumnName();
}