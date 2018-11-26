<?php

namespace SuperV\Platform\Domains\Resource\Field\Types\V2;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;
class Number extends FieldTypeV2 implements NeedsDatabaseColumn
{
    public function getAccessor(): ?Closure
    {
        if ($this->getConfigValue('type') === 'decimal') {
            return function ($value) {
                return (float)number_format(
                    $value,
                    $this->getConfigValue('places'),
                    $this->getConfigValue('dec_point', '.'),
                    $this->getConfigValue('thousands_sep', '')
                );
            };
        }

        return function ($value) { return (int)$value; };
    }

    public function getMutator(): ?Closure
    {
        return $this->getAccessor();
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

    public function getDatabaseColumns($fieldName, $fieldConfig)
    {
        return [
            ['name' => $fieldName, 'type' => $fieldConfig['type']],
        ];
    }
}