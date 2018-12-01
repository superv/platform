<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Filter\ProvidesField;
use SuperV\Platform\Support\Composer\Payload;

class FieldComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field
     */
    protected $field;

    public function __construct($field)
    {
        $this->field = $field instanceof ProvidesField ? $field->makeField() : $field;
    }

    public function forForm($entry = null)
    {
        $field = $this->field;

        if ($entry) {
            $value = $field->resolveFromEntry($entry);

            if ($callback = $field->getCallback('form.accessing')) {
                $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
            }
        }

        $payload = (new Payload([
            'type'  => $field->getType(),
            'uuid'  => $field->uuid(),
            'name'  => $field->getName(),
            'label' => $field->getLabel(),
            'placeholder' => $field->getPlaceholder(),
            'value' => $value ?? null,
        ]))->setFilterNull(true);

        if ($callback = $field->getCallback('form.composing')) {
            app()->call($callback, ['entry' => $entry, 'payload' => $payload]);
        }

        return $payload;
    }

    public function forTableRow($entry)
    {
//        return $this->forForm($entry);

        $field = $this->field;

        $value = $field->resolveFromEntry($entry);

        if ($callback = $field->getCallback('table.accessing')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        if ($callback = $field->getCallback('table.presenting')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        $payload = (new Payload([
            'type'       => $field->getType(),
            'name'       => $field->getColumnName(),
            'value'      => $value,
            'presenting' => true,
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
            'type'  => $field->getType(),
            'uuid'  => $field->uuid(),
            'name'  => $field->getColumnName(),
            'label' => $field->getLabel(),
            'value' => $value,
        ]))->setFilterNull(false);

        if ($callback = $field->getCallback('view.composing')) {
               app()->call($callback, ['entry' => $entry, 'payload' => $payload]);
           }

        return $payload;
    }

    public function forTableConfig()
    {
        $field = $this->field;

        $payload = (new Payload([
            'uuid'  => $field->uuid(),
            'name'  => $field->getName(),
            'label' => $field->getLabel(),
        ]))->setFilterNull(false);

        return $payload;
    }

}