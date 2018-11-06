<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Field\Field;

class Number extends Field
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
}