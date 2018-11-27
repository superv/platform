<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Support\Composer\Composition;

class Select extends FieldType implements NeedsDatabaseColumn
{
    public function getComposer(): ?Closure
    {
        return function (Composition $composition) {
            if ($options = $this->field->getConfigValue('options')) {
                $composition->replace('meta.options', $options);
            }
        };
    }

    public function build(): FieldType
    {
        if (array_has($this->config, 'options')) {
            $this->setConfigValue('options', Select::parseOptions($this->getConfigValue('options')));
        }

        return parent::build();
    }

    public function setOptions(array $options)
    {
        $this->setConfigValue('options', $options);

        return $this;
    }

    public static function parseOptions(array $options = [])
    {
        if (! empty($options) && ! is_array(array_first($options))) {
            return array_map(function ($value) use ($options) {
                return ['value' => $value, 'text' => $options[$value]];
            }, array_keys($options));
        }

        return $options;
    }
}