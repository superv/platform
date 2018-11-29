<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Support\Composer\Composition;

class FieldComposer
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\Field
     */
    protected $field;

    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    public function forForm(?EntryContract $entry = null)
    {
        $field = $this->field;

        if ($entry) {
            $value = $field->resolveFromEntry($entry);

            if ($callback = $field->getCallback('form.accessing')) {
                $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
            }
        }

        $composition = (new Composition([
            'type'  => $field->getType(),
            'uuid'  => $field->uuid(),
            'name'  => $field->getName(),
            'label' => $field->getLabel(),
            'placeholder' => $field->getPlaceholder(),
            'value' => $value ?? null,
        ]))->setFilterNull(true);

        if ($callback = $field->getCallback('form.composing')) {
            app()->call($callback, ['entry' => $entry, 'composition' => $composition]);
        }

        return $composition;
    }

    public function forTableRow(EntryContract $entry)
    {
        $field = $this->field;

        $value = $field->resolveFromEntry($entry);

        if ($callback = $field->getCallback('table.accessing')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        if ($callback = $field->getCallback('table.presenting')) {
            $value = app()->call($callback, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        $composition = (new Composition([
            'type'  => $field->getType(),
            'name'  => $field->getColumnName(),
            'value' => $value,
        ]))->setFilterNull(false);

        if ($callback = $field->getCallback('table.composing')) {
            app()->call($callback, ['entry' => $entry, 'composition' => $composition]);
        }

        return $composition;
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

        $composition = (new Composition([
            'type'  => $field->getType(),
            'uuid'  => $field->uuid(),
            'name'  => $field->getColumnName(),
            'label' => $field->getLabel(),
            'value' => $value,
        ]))->setFilterNull(false);

        if ($callback = $field->getCallback('view.composing')) {
               app()->call($callback, ['entry' => $entry, 'composition' => $composition]);
           }

        return $composition;
    }

    public function forTableConfig()
    {
        $field = $this->field;

        $composition = (new Composition([
            'uuid'  => $field->uuid(),
            'name'  => $field->getName(),
            'label' => $field->getLabel(),
        ]))->setFilterNull(false);

        return $composition;
    }

}