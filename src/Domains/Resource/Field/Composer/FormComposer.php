<?php

namespace SuperV\Platform\Domains\Resource\Field\Composer;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

class FormComposer implements FormComposerInterface
{
    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface */
    protected $field;

    /** @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface */
    protected $form;

    public function compose(): Payload
    {
        return Payload::make([
            'identifier'  => $this->field->getIdentifier(),
            'handle'      => $this->field->getHandle(),
            'type'        => $this->field->getType(),
            'component'   => $this->field->getComponent(),
            'label'       => $this->field->getLabel(),
            'value'       => $this->field->getValue(),
            'placeholder' => $this->field->getPlaceholder(),
            'hint'        => $this->field->getConfigValue('hint'),
            'meta'        => $this->field->getConfigValue('meta'),
            'presenting'  => $this->field->getConfigValue('presenting'),
        ]);
    }

    public function setField(FieldInterface $field): FormComposer
    {
        $this->field = $field;

        return $this;
    }

    public function setForm(FormInterface $form): FormComposer
    {
        $this->form = $form;

        return $this;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}