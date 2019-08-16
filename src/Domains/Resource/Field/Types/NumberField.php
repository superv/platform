<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Field\Contracts\RequiresDbColumn;
use SuperV\Platform\Domains\Resource\Field\Contracts\SortsQuery;
use SuperV\Platform\Domains\Resource\Field\FieldType;

class NumberField extends FieldType implements RequiresDbColumn, SortsQuery
{
    protected function boot()
    {
        $this->field->on('form.accessing', $this->accessor());
        $this->field->on('form.mutating', $this->accessor());

        $this->field->on('view.presenting', $this->accessor());
        $this->field->on('table.presenting', $this->accessor());
    }

    protected function accessor()
    {
        return function ($value) {
            if ($this->getConfigValue('type') === 'decimal') {
                return (float)number_format(
                    $value,
                    $this->getConfigValue('places'),
                    $this->getConfigValue('dec_point', '.'),
                    $this->getConfigValue('thousands_sep', '')
                );
            }

            return (int)$value;
        };
    }

    public function sortQuery($query, $direction)
    {
        $query->orderBy($this->field->getColumnName(), $direction);
    }

    public function makeRules()
    {
        $rules = [];

        $type = $this->getConfigValue('type');
        if ($type === 'integer') {
            $rules[] = 'integer';
        } elseif ($type === 'decimal') {
            $rules[] = 'numeric';
        }
        if ($this->getConfigValue('unsigned') === true) {
            $rules[] = 'min:0';
        }

        return $rules;
    }
}
