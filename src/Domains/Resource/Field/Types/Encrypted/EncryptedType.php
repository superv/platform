<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Encrypted;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\ProvidesFieldComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\FieldRules;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Support\Composer\Payload;

class EncryptedType extends FieldType implements RequiresDbColumn, ProvidesFieldComponent
{
    protected $handle = 'encrypted';

    protected $component = 'sv_encrypted_field';

    public function fieldComposed(Payload $payload, $context = null)
    {
        $payload->set('value', null);
    }

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        if (! $value = $request->get($this->getFieldHandle())) {
            return null;
        }

        $data->set($this->getColumnName(), bcrypt($value));
    }

    public function updateRules(FieldRules $rules)
    {
        $rules->addRule('sometimes');
    }

    public function getComponentName(): string
    {
        return $this->component;
    }
}
