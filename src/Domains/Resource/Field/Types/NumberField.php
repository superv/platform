<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\Field;

class NumberField extends Field implements NeedsDatabaseColumn
{
    protected function boot()
    {
        $this->on('form.accessing', $this->accessor());
        $this->on('form.mutating', $this->accessor());

        $this->on('view.presenting', $this->accessor());
        $this->on('table.presenting', $this->accessor());
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