<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Support\Composer\Composition;

class Select extends FieldType implements NeedsDatabaseColumn
{
    protected function composer()
    {
        return function (Composition $composition) {
            if ($options = $this->field->getConfigValue('options')) {
                $composition->replace('meta.options', $options);
            }
        };
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

    protected function boot()
    {
        $this->on('form.composing', $this->composer());
    }
}