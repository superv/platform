<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FieldComposer;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

class FormFieldComposer implements FieldComposer
{
    public function toForm(FormInterface $form, FormField $field)
    {
        $payload = (new Payload([
            'identifier'  => $field->getIdentifier(),
            'type'        => $field->getType(),
            'revision_id' => $field->revisionId(),
            'name'        => $field->getName(),
            'label'       => $field->getLabel(),
            'placeholder' => $field->getPlaceholder(),
            'value'       => $value ?? $field->getValue(),
            'hint'        => $field->getConfigValue('hint'),
            'meta'        => $field->getConfigValue('meta'),
            'presenting'  => $field->getConfigValue('presenting'),

        ]))->setFilterNull(true);

        return $payload->get();
    }
}
