<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Support\Composer\Payload;

interface FieldTypeInterface
{
    public function getConfigValue($key, $default = null);

    public function getHandle(): ?string;

    public function saving(FormInterface $form);

    public function saved(FormInterface $form);

    public function formComposed(Payload $formPayload, FormInterface $form);

    public function fieldComposed(Payload $payload, $context = null);

    public function setField(FieldInterface $field): FieldTypeInterface;

    public function getFieldHandle();

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null);

    public function resolveDataFromEntry(FormData $data, EntryContract $entry);

    public function getConfig();

    public function addFlag($flag);

    public function getColumnName();

    public function getComponent(): ?string;
}