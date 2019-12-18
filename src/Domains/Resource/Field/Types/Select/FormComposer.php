<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\Select;

use SuperV\Platform\Domains\Resource\Field\Composer\FormComposer as BaseFormComposer;
use SuperV\Platform\Domains\Resource\Field\Composer\FormComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

class FormComposer implements FormComposerInterface
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Composer\FormComposer
     */
    protected $base;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface
     */
    protected $form;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    public function __construct(BaseFormComposer $composer, ?FormInterface $form, FieldInterface $field)
    {
        $this->base = $composer;
        $this->form = $form;
        $this->field = $field;
    }

    public function compose(): Payload
    {
        $payload = $this->base->compose();

        $options = SelectType::parseOptions(($this->field->getConfigValue('options', [])));

        $payload->set('meta.options', $options);
        $payload->set('placeholder', __('Select :Object', ['object' => $this->field->getPlaceholder()]));

        return $payload;
    }
}