<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use SuperV\Platform\Domains\Resource\Contracts\NeedsDatabaseColumn;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesFilter;
use SuperV\Platform\Domains\Resource\Filter\SelectFilter;
use SuperV\Platform\Support\Composer\Composition;

class Select extends FieldType implements NeedsDatabaseColumn, ProvidesFilter
{
    protected $placeholder;

    protected function boot()
    {
        $this->on('form.composing', $this->composer());
    }

    protected function composer()
    {
        return function (Composition $composition) {
            $options = array_merge([['value' => null, 'text' => $this->getPlaceholder()]],
                static::parseOptions(($this->getOptions()))
            );

            $composition->set('meta.options', $options);
        };
    }

    public function makeFilter()
    {
        return SelectFilter::make($this->getName(), $this->getLabel())
                           ->setOptions($this->getOptions());
    }

    public function getOptions()
    {
        return $this->field->getConfigValue('options', []);
    }

    public function getPlaceholder()
    {
        return $this->field->getPlaceholder() ?? $this->getLabel();
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