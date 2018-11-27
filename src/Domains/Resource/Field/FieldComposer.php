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

    public function forForm(?EntryContract $entry)
    {
        $field = $this->field;

        if ($entry) {
            $value = $field->resolveFromEntry($entry);

            if ($accessor = $field->getAccessor()) {
                $value = app()->call($accessor, ['entry' => $entry, 'value' => $value, 'field' => $field]);
            }

            if ($presenter = $field->getPresenter()) {
                $value = app()->call($presenter, ['entry' => $entry, 'value' => $value, 'field' => $field]);
            }
        }

        $composition = (new Composition([
            'type'   => $field->getType(),
            'uuid'   => $field->uuid(),
            'name'   => $field->getColumnName(),
            'label'  => $field->getLabel(),
            'value'  => $value ?? null,
            'config' => $field->getConfig(),
        ]))->setFilterNull(false);

        if ($composer = $field->getComposer()) {
            app()->call($composer, ['entry' => $entry, 'composition' => $composition]);
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

    public function forTableRow(EntryContract $entry)
    {
        $field = $this->field;

        $value = $field->resolveFromEntry($entry);

        if ($accessor = $field->getAccessor()) {
            $value = app()->call($accessor, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        if ($presenter = $field->getPresenter()) {
            $value = app()->call($presenter, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        $composition = (new Composition([
            'type'  => $field->getType(),
            'name'  => $field->getColumnName(),
            'value' => $value,
        ]))->setFilterNull(false);

        if ($composer = $field->getComposer()) {
            app()->call($composer, ['entry' => $entry, 'composition' => $composition]);
        }

        return $composition;
    }

    public function forView(EntryContract $entry)
    {
        $field = $this->field;

        $value = $field->resolveFromEntry($entry);

        if ($accessor = $field->getAccessor()) {
            $value = app()->call($accessor, ['entry' => $entry, 'value' => $value, 'field' => $field]);
        }

        $composition = (new Composition([
            'type'  => $field->getType(),
            'uuid'  => $field->uuid(),
            'name'  => $field->getColumnName(),
            'label' => $field->getLabel(),
            'value' => $value,
        ]))->setFilterNull(false);

        if ($composer = $field->getComposer()) {
            app()->call($composer, ['entry' => $entry, 'composition' => $composition]);
        }

        return $composition;
    }
}