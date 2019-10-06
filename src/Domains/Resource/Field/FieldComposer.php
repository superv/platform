<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Filter\ProvidesField;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasPresenter;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Support\Composer\Payload;

class FieldComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    public function __construct($field)
    {
        $this->field = $field instanceof ProvidesField ? $field->makeField() : $field;
    }

    public function forForm(Form $form = null)
    {
        $field = $this->field;

        $entry = $form ? $form->getEntry() : null;

        if ($entry) {
            $value = $field->resolveFromEntry($entry);

            if ($field instanceof HasAccessor) {
                $value = (new Accessor($field))->get(['entry' => $entry, 'value' => $value]);
            } elseif ($callback = $field->getCallback('form.accessing')) {
                $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
            }
        }

        if ($form && ! isset($value)) {
            $value = $form->getData()->getForDisplay($field->getName());
        }

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

        if ($callback = $field->getCallback('form.composing')) {
            app()->call($callback, ['form' => $form, 'entry' => $entry, 'payload' => $payload]);
        }

        return $payload;
    }

    public function forTableConfig()
    {
        $field = $this->field;

        $payload = (new Payload([
            'identifier'  => $field->getIdentifier(),
            'revision_id' => $field->revisionId(),
            'name'        => $field->getName(),
            'label'       => $field->getLabel(),
            'classes'     => $field->getConfigValue('classes'),
            'sortable'    => $field->getFieldType() instanceof SortsQuery,
        ]))->setFilterNull(false);

        return $payload;
    }

    public function forTableRow($entry)
    {
        $field = $this->field;

        $value = $field->resolveFromEntry($entry);

        if ($callback = $field->getCallback('table.accessing')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        if ($field instanceof HasPresenter) {
            $value = app()->call($field->getPresenter(), ['entry' => $entry, 'value' => $value]);
        } elseif ($callback = $field->getCallback('table.presenting')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        $payload = (new Payload([
            'identifier'  => $field->getIdentifier(),
            'revision_id' => $field->revisionId(),
            'type'        => $field->getType(),
            'name'        => $field->getColumnName(),
            'value'       => $value,
            'presenting'  => true,
            'classes'     => $field->getConfigValue('classes'),
        ]))->setFilterNull(false);

        if ($callback = $field->getCallback('table.composing')) {
            app()->call($callback, ['entry' => $entry, 'payload' => $payload]);
        }

        return $payload;
    }

    public function forView(EntryContract $entry)
    {
        $field = $this->field;

        $value = $field->resolveFromEntry($entry);

        if ($callback = $field->getCallback('view.accessing')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        if ($callback = $field->getCallback('view.presenting')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        $payload = (new Payload([
            'identifier'  => $field->getIdentifier(),
            'type'        => $field->getType(),
            'revision_id' => $field->revisionId(),
            'name'        => $field->getColumnName(),
            'label'       => $field->getLabel(),
            'value'       => $value,
            'presenting'  => true,
            'classes'     => $field->getConfigValue('classes'),
        ]))->setFilterNull(false);

        if ($callback = $field->getCallback('view.composing')) {
            app()->call($callback, ['entry' => $entry, 'payload' => $payload]);
        }

        return $payload;
    }
}
