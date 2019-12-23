<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\ComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;

class FieldComposer implements ComposerInterface
{
    /** @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface */
    protected $field;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface
     */
    protected $fieldType;

    /** @var \SuperV\Platform\Support\Composer\Payload */
    protected $payload;

    public function __construct(FieldInterface $field)
    {
        $this->field = $field;
        $this->fieldType = $field->getFieldType();
        $this->payload = Payload::make([
            'identifier' => $this->field->getIdentifier(),
            'handle'     => $this->field->getHandle(),
            'type'       => $this->field->getType(),
            'component'  => $this->field->getComponent(),
            'label'      => $this->field->getLabel(),
        ]);
    }

    public function compose(): Payload
    {
        return $this->getPayload();
    }

    public function toForm(FormInterface $form = null): Payload
    {
        if ($form) {
            if ($entry = $form->getEntry()) {
                $value = $this->field->getValue()->setEntry($entry)->resolve()->get();
            }
        }
//        if ($form && ! isset($value)) {
//            $value = $form->getData()->getForDisplay($this->getFieldHandle());
//        }

        $this->payload->merge([
            'value'       => $value ?? $this->field->getDefaultValue(),
            'placeholder' => $this->field->getPlaceholder(),
            'hint'        => $this->field->getConfigValue('hint'),
            'meta'        => $this->field->getConfigValue('meta'),
            'presenting'  => $this->field->getConfigValue('presenting'),
        ]);

        $this->form($form);

        $this->fieldType->fieldComposed($this->payload, $form);

        return $this->payload;
    }

    public function toTable(?EntryContract $entry = null): Payload
    {
//        $value = $this->field->resolveFromEntry($entry);
//        $value = $this->fieldType->getValue($entry);

        $value = $this->field->getValue()->setEntry($entry)->resolve()->get();

        if (! $entry) {
            $this->payload->merge([
                'sortable' => $this->fieldType instanceof SortsQuery,
            ]);
        } else {
            if ($this->fieldType instanceof HasAccessor) {
                $value = (new Accessor($this->fieldType))->get(['entry' => $entry, 'value' => $value]);
            }
            if ($callback = $this->field->getCallback('table.presenting')) {
                $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $this->field]);
            }

            $this->payload->merge([
                'value'      => $value,
                'presenting' => true,
            ]);
        }

        $this->payload->merge([
            'classes' => $this->field->getConfigValue('classes'),
        ]);

        $this->table($entry);

        return $this->payload;
    }

    public function toView(EntryContract $entry): Payload
    {
//        $value = $this->field->resolveFromEntry($entry);
//        $value = $this->fieldType->getValue($entry);
        $value = $this->field->getValue()->setEntry($entry)->resolve()->get();
//        if ($callback = $this->field->getCallback('view.presenting')) {
//            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $this->field]);
//        }

//        if ($this->fieldType instanceof HasAccessor) {
//            $value = (new Accessor($this->fieldType))->get(['entry' => $entry, 'value' => $value]);
//        }

        $payload = $this->compose()->merge([
            'value'      => $value,
            'classes'    => $this->field->getConfigValue('classes'),
            'presenting' => true,
        ]);

//        if ($callback = $this->field->getCallback('view.composing')) {
//            app()->call($callback, ['entry' => $entry, 'payload' => $payload]);
//        }

        $this->view($entry);

        return $payload;
    }

    public function table(?EntryContract $entry = null): void { }

    public function view(EntryContract $entry): void { }

    public function form(?FormInterface $form = null): void { }

    public function getPayload(): Payload
    {
        return $this->payload;
    }

    protected function getConfigValue($key, $default = null)
    {
        return $this->field->getConfigValue($key, $default);
    }

    protected function getFieldHandle(): string
    {
        return $this->field->getHandle();
    }
}